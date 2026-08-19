<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionAdjustment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'commission_id',
        'type',
        'amount',
        'reason',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function commission()
    {
        return $this->belongsTo(Commission::class);
    }
}
