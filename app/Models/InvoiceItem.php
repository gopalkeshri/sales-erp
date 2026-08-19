<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'hsn_code',
        'description',
        'quantity',
        'unit_price',
        'discount_percent',
        'taxable_value',
        'tax_rate',
        'cgst_rate',
        'cgst_amount',
        'sgst_rate',
        'sgst_amount',
        'igst_rate',
        'igst_amount',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'taxable_value' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'cgst_rate' => 'decimal:2',
            'cgst_amount' => 'decimal:2',
            'sgst_rate' => 'decimal:2',
            'sgst_amount' => 'decimal:2',
            'igst_rate' => 'decimal:2',
            'igst_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
