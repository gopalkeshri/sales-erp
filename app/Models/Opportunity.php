<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'customer_id',
        'contact_id',
        'lead_id',
        'stage',
        'probability',
        'amount',
        'expected_revenue',
        'currency',
        'close_date',
        'actual_close_date',
        'lost_reason',
        'assigned_to',
        'team_id',
        'territory_id',
        'competitors',
        'decision_criteria',
        'next_step',
        'custom_fields',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'probability' => 'integer',
            'amount' => 'decimal:2',
            'expected_revenue' => 'decimal:2',
            'close_date' => 'date',
            'actual_close_date' => 'date',
            'competitors' => 'array',
            'custom_fields' => 'array',
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

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function territory()
    {
        return $this->belongsTo(Territory::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function opportunityProducts()
    {
        return $this->hasMany(OpportunityProduct::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'opportunity_products')
            ->withPivot('quantity', 'unit_price', 'discount', 'total')
            ->withTimestamps();
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class, 'entity_id')->where('entity_type', 'opportunity');
    }
}
