<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'street',
        'city',
        'state',
        'country',
        'postal_code',
        'manager_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'inventory')
            ->withPivot('quantity', 'reserved_quantity', 'last_restocked_at')
            ->withTimestamps();
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
