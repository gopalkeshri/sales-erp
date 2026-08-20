<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('inventories.warehouse');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('sku', 'like', "%{$s}%")
                  ->orWhere('category', 'like', "%{$s}%");
            });
        }

        $products = $query->orderBy('name')->paginate($request->get('per_page', 15));
        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string|unique:products,sku|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'type' => 'nullable|in:product,service',
            'unit_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'unit' => 'nullable|string|max:20',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'hsn_code' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
            'min_stock_level' => 'nullable|integer|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'image' => 'nullable|string|max:255',
            'attributes' => 'nullable|array',
            'initial_quantity' => 'nullable|integer|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        $initialQty = (int)($validated['initial_quantity'] ?? 0);
        $warehouseId = $validated['warehouse_id'] ?? null;
        unset($validated['initial_quantity'], $validated['warehouse_id']);

        $product = Product::create($validated);

        if ($product->type === 'product' && $initialQty > 0 && $warehouseId) {
            $inventory = \App\Models\Inventory::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouseId],
                ['quantity' => 0, 'reserved_quantity' => 0]
            );
            $inventory->increment('quantity', $initialQty);
            $inventory->update(['last_restocked_at' => now()]);

            \App\Models\InventoryTransaction::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'type' => 'in',
                'quantity' => $initialQty,
                'reference_type' => 'initial_inward',
                'reference_id' => $product->id,
                'notes' => "Initial inward stock cataloging for {$product->sku}",
                'performed_by' => $request->user() ? $request->user()->id : null,
            ]);
        }

        return response()->json($product->load('inventories.warehouse'), 201);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::with(['inventories.warehouse', 'transactions.warehouse', 'transactions.performer'])->findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $validated = $request->validate([
            'sku' => 'sometimes|required|string|max:50|unique:products,sku,' . $id,
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'type' => 'nullable|in:product,service',
            'unit_price' => 'sometimes|required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'unit' => 'nullable|string|max:20',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'hsn_code' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
            'min_stock_level' => 'nullable|integer|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'image' => 'nullable|string|max:255',
            'attributes' => 'nullable|array',
        ]);

        $product->update($validated);
        return response()->json($product);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully.']);
    }
}
