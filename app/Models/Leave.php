<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Laravel usually guesses 'leaves' correctly, but explicit is fine.
     *
     * @var string
     */
    // protected $table = 'leaves'; // Usually not needed

    /**
     * The attributes that are mass assignable.
     * Use the ACTUAL column names from your migration.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id', // Correct name
        'leave_type',
        'start_date', // Correct name
        'end_date',   // Correct name
        'reason',
        'document',
        'status',     // Include status if you ever mass assign it
        'number_of_days',  // (though usually status is set programmatically)
        'remarks', // <<< ADD THIS LINE
    ];

    /**
     * The attributes that should be cast.
     * Use the ACTUAL date column names.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date', // Correct name
        'end_date' => 'date',   // Correct name
        'leave_type' => 'string', // Enums are often treated as strings by Eloquent
        'status' => 'string',
    ];

    /**
     * Get the user (student) that owns the leave.
     * Use the correct foreign key name 'student_id'.
     */
    public function student(): BelongsTo
    {
        // Explicitly state the foreign key if it's not the default 'user_id'
        return $this->belongsTo(User::class, 'student_id');
    }

    // Define relationships for HOD, DSA, SSO if needed later
}