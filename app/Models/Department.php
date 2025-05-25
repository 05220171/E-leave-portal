<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;    // This is correctly imported

class Department extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        // Add any other fillable attributes for your department
    ];

    /**
     * Get the users associated with this department.
     * A department can have many users.
     */
    public function users(): HasMany
    {
        // This assumes your 'users' table has a 'department_id' foreign key
        // that references the 'id' of this department.
        return $this->hasMany(User::class, 'department_id');
    }

    /**
     * Get the programs offered by this department.
     * A department can offer many programs.
     */
    public function programs(): HasMany // Added return type hint for clarity
    {
        // This assumes your 'programs' table has a 'department_id' foreign key
        // that references the 'id' of this department.
        // Laravel will infer 'department_id' if you follow conventions.
        // You can explicitly state it if needed: return $this->hasMany(Program::class, 'department_id');
        return $this->hasMany(Program::class);
    }

    // You can add other model-specific logic, accessors, mutators, etc., here
}