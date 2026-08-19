<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Models\Warehouse;
use App\Services\InventoryService;

class UpdateInventoryOnOrder
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function handle(OrderPlaced $event): void
    {
        // When order is placed, check default warehouse and reserve/deduct if specified
        $defaultWarehouse = Warehouse::where('is_active', true)->first();
        if ($defaultWarehouse && in_array($event->order->status, ['confirmed', 'processing', 'shipped'])) {
            foreach ($event->order->items as $item) {
                try {
                    $this->inventoryService->stockOut(
                        $item->product_id,
                        $defaultWarehouse->id,
                        $item->quantity,
                        'order_fulfillment',
                        $event->order->id,
                        "Order fulfillment: {$event->order->order_number}"
                    );
                    $item->update(['shipped_quantity' => $item->quantity]);
                } catch (\Exception $e) {
                    // Handled gracefully
                }
            }
        }
    }
}
