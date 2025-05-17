<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the workflow steps for the leave type.
     */
    public function leaveWorkflows(): HasMany
    {
        // Order by step_number to ensure they are in the correct sequence
        return $this->hasMany(LeaveWorkflow::class)->orderBy('step_number');
    }

    /**
     * Get the leave requests associated with this leave type.
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }
}