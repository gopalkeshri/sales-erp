<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'order_id',
        'customer_id',
        'contact_id',
        'status',
        'type',
        'invoice_date',
        'due_date',
        'subtotal',
        'discount_total',
        'tax_total',
        'total',
        'amount_paid',
        'balance_due',
        'currency',
        'payment_terms',
        'notes',
        'assigned_to',
        'territory_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance_due' => 'decimal:2',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
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
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function recalculatePaymentStatus(): void
    {
        $paid = (float) $this->payments()->sum('amount');
        $balance = max(0, (float) $this->total - $paid);

        $status = $this->status;
        if ($balance <= 0 && $this->total > 0) {
            $status = 'paid';
        } elseif ($paid > 0 && $balance > 0) {
            $status = 'partial';
        } elseif ($this->due_date && $this->due_date < now()->toDateString() && $status !== 'paid') {
            $status = 'overdue';
        }

        $this->update([
            'amount_paid' => $paid,
            'balance_due' => $balance,
            'status' => $status,
        ]);
    }
}
