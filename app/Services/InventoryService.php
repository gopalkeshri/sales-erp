<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    public function stockIn(int $productId, int $warehouseId, int $quantity, ?string $notes = null, ?User $user = null): Inventory
    {
        return DB::transaction(function () use ($productId, $warehouseId, $quantity, $notes, $user) {
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['quantity' => 0, 'reserved_quantity' => 0]
            );

            $inventory->increment('quantity', $quantity);
            $inventory->update(['last_restocked_at' => now()]);

            InventoryTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'type' => 'in',
                'quantity' => $quantity,
                'notes' => $notes ?: 'Stock In',
                'performed_by' => $user ? $user->id : null,
                'created_at' => now(),
            ]);

            return $inventory->fresh(['product', 'warehouse']);
        });
    }

    public function stockOut(int $productId, int $warehouseId, int $quantity, ?string $referenceType = null, ?int $referenceId = null, ?string $notes = null, ?User $user = null): Inventory
    {
        return DB::transaction(function () use ($productId, $warehouseId, $quantity, $referenceType, $referenceId, $notes, $user) {
            $inventory = Inventory::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if (!$inventory || $inventory->quantity < $quantity) {
                throw new Exception("Insufficient inventory for product ID {$productId} in warehouse ID {$warehouseId}.");
            }

            $inventory->decrement('quantity', $quantity);

            InventoryTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'type' => 'out',
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes ?: 'Stock Out',
                'performed_by' => $user ? $user->id : null,
                'created_at' => now(),
            ]);

            return $inventory->fresh(['product', 'warehouse']);
        });
    }

    public function transferStock(int $productId, int $fromWarehouseId, int $toWarehouseId, int $quantity, ?string $notes = null, ?User $user = null): array
    {
        return DB::transaction(function () use ($productId, $fromWarehouseId, $toWarehouseId, $quantity, $notes, $user) {
            $sourceInventory = Inventory::where('product_id', $productId)
                ->where('warehouse_id', $fromWarehouseId)
                ->first();

            if (!$sourceInventory || $sourceInventory->quantity < $quantity) {
                throw new Exception("Insufficient stock in source warehouse.");
            }

            $sourceInventory->decrement('quantity', $quantity);

            $targetInventory = Inventory::firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $toWarehouseId],
                ['quantity' => 0, 'reserved_quantity' => 0]
            );
            $targetInventory->increment('quantity', $quantity);
            $targetInventory->update(['last_restocked_at' => now()]);

            InventoryTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => $fromWarehouseId,
                'type' => 'transfer',
                'quantity' => -$quantity,
                'reference_type' => 'warehouse_transfer_out',
                'reference_id' => $toWarehouseId,
                'notes' => $notes ?: "Transferred to warehouse ID {$toWarehouseId}",
                'performed_by' => $user ? $user->id : null,
                'created_at' => now(),
            ]);

            InventoryTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => $toWarehouseId,
                'type' => 'transfer',
                'quantity' => $quantity,
                'reference_type' => 'warehouse_transfer_in',
                'reference_id' => $fromWarehouseId,
                'notes' => $notes ?: "Received from warehouse ID {$fromWarehouseId}",
                'performed_by' => $user ? $user->id : null,
                'created_at' => now(),
            ]);

            return [
                'source' => $sourceInventory->fresh(['product', 'warehouse']),
                'target' => $targetInventory->fresh(['product', 'warehouse']),
            ];
        });
    }

    public function adjustStock(int $productId, int $warehouseId, int $newQuantity, ?string $reason = null, ?User $user = null): Inventory
    {
        return DB::transaction(function () use ($productId, $warehouseId, $newQuantity, $reason, $user) {
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['quantity' => 0, 'reserved_quantity' => 0]
            );

            $diff = $newQuantity - $inventory->quantity;
            $inventory->update(['quantity' => $newQuantity]);

            InventoryTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'type' => 'adjustment',
                'quantity' => $diff,
                'notes' => $reason ?: 'Stock manual adjustment',
                'performed_by' => $user ? $user->id : null,
                'created_at' => now(),
            ]);

            return $inventory->fresh(['product', 'warehouse']);
        });
    }

    public function getLowStockProducts(?int $warehouseId = null)
    {
        $query = Inventory::with(['product', 'warehouse'])
            ->join('products', 'inventory.product_id', '=', 'products.id')
            ->where(function ($q) {
                $q->whereColumn('inventory.quantity', '<=', 'products.min_stock_level')
                  ->orWhereColumn('inventory.quantity', '<=', 'products.reorder_point');
            })
            ->select('inventory.*');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->get();
    }
}
