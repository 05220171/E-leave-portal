<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_id',
        'user_id',
        'workflow_step_number',
        'acted_as_role',
        'action_taken',
        'remarks',
        'action_at',
    ];

    protected $casts = [
        'action_at' => 'datetime',
    ];

    public function leave(): BelongsTo
    {
        return $this->belongsTo(Leave::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class); // The user who performed the action
    }
}