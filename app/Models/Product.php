<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'category',
        'subcategory',
        'type',
        'unit_price',
        'cost_price',
        'currency',
        'unit',
        'tax_rate',
        'hsn_code',
        'is_active',
        'min_stock_level',
        'reorder_point',
        'image',
        'attributes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'is_active' => 'boolean',
            'attributes' => 'array',
            'min_stock_level' => 'integer',
            'reorder_point' => 'integer',
        ];
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'inventory')
            ->withPivot('quantity', 'reserved_quantity', 'last_restocked_at')
            ->withTimestamps();
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function getTotalStockAttribute(): int
    {
        return (int) $this->inventories()->sum('quantity');
    }

    public function getAvailableStockAttribute(): int
    {
        return (int) $this->inventories()->sum(\Illuminate\Support\Facades\DB::raw('quantity - reserved_quantity'));
    }
}
