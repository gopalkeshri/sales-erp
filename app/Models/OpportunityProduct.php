<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpportunityProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'opportunity_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
