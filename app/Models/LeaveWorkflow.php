<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_type_id',
        'step_number',
        'approver_role',
        'action_type', // 'approval' or 'record_keeping'
    ];

    /**
     * Get the leave type that this workflow step belongs to.
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    // You might add a relationship to User model if you want to link
    // a specific user to a role for a step, but for now, role string is fine.
}