<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'trade_name',
        'gst_number',
        'pan_number',
        'industry',
        'website',
        'phone',
        'email',
        'address_street',
        'address_city',
        'address_state',
        'state_code',
        'address_country',
        'address_postal_code',
        'billing_street',
        'billing_city',
        'billing_state',
        'billing_country',
        'billing_postal_code',
        'shipping_street',
        'shipping_city',
        'shipping_state',
        'shipping_state_code',
        'shipping_country',
        'shipping_postal_code',
        'type',
        'status',
        'assigned_to',
        'territory_id',
        'tags',
        'notes',
        'credit_limit',
        'payment_terms',
        'currency',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'credit_limit' => 'decimal:2',
        ];
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

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function primaryContact()
    {
        return $this->hasOne(Contact::class)->where('is_primary', true);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
