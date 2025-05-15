<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <-- Good to add
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;    // <-- IMPORT THIS

class Department extends Model
{
    use HasFactory; // <-- Good to add

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        // Add any other fields your departments table has that you want to be mass assignable
        // e.g., 'hod_id', 'description', etc.
    ];

    /**
     * Get the users associated with the department.
     */
    public function users(): HasMany // <-- ADDED INVERSE RELATIONSHIP
    {
        // Assumes 'users' table has 'department_id' foreign key
        return $this->hasMany(User::class, 'department_id');
    }
}