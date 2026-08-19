<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period',
        'period_type',
        'total_sales',
        'commission_rate',
        'commission_amount',
        'bonus_amount',
        'status',
        'approved_by',
        'approved_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'total_sales' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'bonus_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function adjustments()
    {
        return $this->hasMany(CommissionAdjustment::class);
    }

    public function getNetPayoutAttribute(): float
    {
        $adjustmentsSum = (float) $this->adjustments()->sum(\Illuminate\Support\Facades\DB::raw("CASE WHEN type = 'penalty' THEN -amount ELSE amount END"));
        return (float) $this->commission_amount + (float) $this->bonus_amount + $adjustmentsSum;
    }
}
