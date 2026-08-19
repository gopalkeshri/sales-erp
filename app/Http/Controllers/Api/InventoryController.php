<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = Inventory::with(['product', 'warehouse']);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('product', function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%");
            });
        }

        $inventories = $query->paginate($request->get('per_page', 15));
        return response()->json($inventories);
    }

    public function stockIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            $inventory = $this->inventoryService->stockIn(
                (int)$validated['product_id'],
                (int)$validated['warehouse_id'],
                (int)$validated['quantity'],
                $validated['notes'] ?? null,
                $request->user()
            );

            return response()->json([
                'message' => 'Stock added successfully.',
                'inventory' => $inventory,
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $inventory = Inventory::findOrFail($id);
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string',
        ]);

        try {
            $updated = $this->inventoryService->adjustStock(
                $inventory->product_id,
                $inventory->warehouse_id,
                (int)$validated['quantity'],
                $validated['reason'] ?? null,
                $request->user()
            );

            return response()->json([
                'message' => 'Stock adjusted successfully.',
                'inventory' => $updated,
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function lowStock(Request $request): JsonResponse
    {
        $warehouseId = $request->get('warehouse_id');
        $lowStock = $this->inventoryService->getLowStockProducts($warehouseId ? (int)$warehouseId : null);
        return response()->json($lowStock);
    }

    public function transfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            $result = $this->inventoryService->transferStock(
                (int)$validated['product_id'],
                (int)$validated['from_warehouse_id'],
                (int)$validated['to_warehouse_id'],
                (int)$validated['quantity'],
                $validated['notes'] ?? null,
                $request->user()
            );

            return response()->json([
                'message' => 'Stock transferred successfully between warehouses.',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function warehouses(): JsonResponse
    {
        $warehouses = Warehouse::with('manager')->where('is_active', true)->get();
        return response()->json($warehouses);
    }
}
