<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'contact_id',
        'opportunity_id',
        'quote_id',
        'status',
        'billing_street',
        'billing_city',
        'billing_state',
        'billing_country',
        'billing_postal_code',
        'shipping_street',
        'shipping_city',
        'shipping_state',
        'shipping_country',
        'shipping_postal_code',
        'subtotal',
        'discount_total',
        'tax_total',
        'shipping_cost',
        'total',
        'currency',
        'payment_terms',
        'expected_delivery_date',
        'actual_delivery_date',
        'assigned_to',
        'territory_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'expected_delivery_date' => 'date',
            'actual_delivery_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
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

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function territory()
    {
        return $this->belongsTo(Territory::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
