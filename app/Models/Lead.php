<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'source',
        'source_detail',
        'customer_id',
        'company_name',
        'contact_name',
        'email',
        'phone',
        'status',
        'qualification_score',
        'assigned_to',
        'territory_id',
        'estimated_value',
        'currency',
        'expected_close_date',
        'notes',
        'converted_at',
        'converted_to_opportunity_id',
        'custom_fields',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'qualification_score' => 'integer',
            'estimated_value' => 'decimal:2',
            'expected_close_date' => 'date',
            'converted_at' => 'datetime',
            'custom_fields' => 'array',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function territory()
    {
        return $this->belongsTo(Territory::class);
    }

    public function convertedOpportunity()
    {
        return $this->belongsTo(Opportunity::class, 'converted_to_opportunity_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class, 'entity_id')->where('entity_type', 'lead');
    }
}
