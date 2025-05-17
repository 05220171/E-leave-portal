<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\LeaveRequestApproval;

class Leave extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * These should match the columns in your 'leaves' table that you want to fill.
     * Ensure all new columns from your 'modify_leaves_table_for_dynamic_workflows' migration
     * that you intend to set during creation are listed here.
     */
    protected $fillable = [
        'student_id',
        'leave_type_id',          // NEW: Foreign key to leave_types table
        'start_date',
        'end_date',
        'reason',
        'document',               // Path to the uploaded document
        'number_of_days',
        'remarks',                // General remarks column

        // Workflow and new status columns
        'current_step_number',    // NEW: Current step in this leave_type's workflow
        'current_approver_role',  // NEW: Role of the current approver
        'overall_status',         // NEW: e.g., 'awaiting_hod_approval', 'approved', 'rejected_by_dsa', 'cancelled'
        'final_remarks',          // NEW: For overall remarks after workflow completion
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'leave_type_id' => 'integer', // Good practice to cast foreign keys
        'current_step_number' => 'integer',
        'is_active' => 'boolean',     // If you have an 'is_active' column on the 'leaves' table itself
    ];

    /**
     * Get the user (student) that owns the leave request.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the specific leave type for this leave request.
     * Renamed to 'type' to avoid potential conflicts if an old 'leave_type' column (string/enum) still exists.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    /**
     * Get the approval/action history for this leave request.
     * This will link to the 'leave_request_approvals' table we'll define later.
     */
    public function approvalActions(): HasMany // Renamed for clarity from 'approvals'
    {
        return $this->hasMany(LeaveRequestApproval::class)->orderBy('action_at', 'asc');
        // For now, if you are still using your 'leave_histories' table and have a LeaveHistory model:
        // return $this->hasMany(LeaveHistory::class)->orderBy('created_at');
        // If no model/table for this yet, you can comment it out or return an empty relation:
        
    }
}