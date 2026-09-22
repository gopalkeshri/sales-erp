<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashfreeOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'order_id',
        'cf_order_id',
        'payment_session_id',
        'order_amount',
        'order_currency',
        'status',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'cf_payment_id',
        'payment_method',
        'bank_reference',
        'payment_time',
        'raw_response',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'order_amount' => 'decimal:2',
            'payment_time' => 'datetime',
            'raw_response' => 'array',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'cashfree_order_id', 'order_id');
    }
}
