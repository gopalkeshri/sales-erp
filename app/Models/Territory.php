<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Territory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'parent_territory_id',
        'manager_id',
        'region',
        'country',
        'state',
        'city',
        'postal_codes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'postal_codes' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(Territory::class, 'parent_territory_id');
    }

    public function children()
    {
        return $this->hasMany(Territory::class, 'parent_territory_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }
}
