<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_number',
        'customer_id',
        'contact_id',
        'opportunity_id',
        'status',
        'valid_until',
        'place_of_supply',
        'state_code',
        'gst_type',
        'subtotal',
        'discount_total',
        'tax_total',
        'cgst_total',
        'sgst_total',
        'igst_total',
        'is_reverse_charge',
        'total',
        'currency',
        'notes',
        'terms_conditions',
        'assigned_to',
        'territory_id',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'converted_to_order_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'valid_until' => 'date',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'cgst_total' => 'decimal:2',
            'sgst_total' => 'decimal:2',
            'igst_total' => 'decimal:2',
            'is_reverse_charge' => 'boolean',
            'total' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function territory()
    {
        return $this->belongsTo(Territory::class);
    }

    public function convertedOrder()
    {
        return $this->belongsTo(Order::class, 'converted_to_order_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function recalculateTotals(): void
    {
        $subtotal = 0;
        $discountTotal = 0;
        $taxTotal = 0;

        foreach ($this->items as $item) {
            $itemBase = $item->quantity * $item->unit_price;
            $itemDiscount = $itemBase * ($item->discount_percent / 100);
            $afterDiscount = $itemBase - $itemDiscount;
            $itemTax = $afterDiscount * ($item->tax_rate / 100);

            $subtotal += $itemBase;
            $discountTotal += $itemDiscount;
            $taxTotal += $itemTax;
        }

        $this->update([
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'total' => ($subtotal - $discountTotal) + $taxTotal,
        ]);
    }
}
