<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'territory_id',
        'team_id',
        'is_active',
        'avatar',
        'phone',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function territory()
    {
        return $this->belongsTo(Territory::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class, 'assigned_to');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'assigned_to');
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class, 'assigned_to');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'assigned_to');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'assigned_to');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class, 'performed_by');
    }

    public function assignedActivities()
    {
        return $this->hasMany(Activity::class, 'assigned_to');
    }

    public function hasRole(string|array $roles): bool
    {
        $roles = (array) $roles;
        if (in_array($this->role, $roles)) {
            return true;
        }
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function hasPermission(string $permissionName): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })
            ->exists();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->hasRole('admin');
    }

    public function isManager(): bool
    {
        return $this->role === 'manager' || $this->hasRole('manager');
    }

    public function isSalesRep(): bool
    {
        return $this->role === 'sales_rep' || $this->hasRole('sales_rep');
    }
}
