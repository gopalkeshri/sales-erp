<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'subject',
        'description',
        'entity_type',
        'entity_id',
        'performed_by',
        'assigned_to',
        'scheduled_at',
        'completed_at',
        'duration',
        'outcome',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'duration' => 'integer',
        ];
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function attachments()
    {
        return $this->hasMany(ActivityAttachment::class);
    }
}
