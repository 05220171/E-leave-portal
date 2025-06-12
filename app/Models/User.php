<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- IMPORT THIS

class User extends Authenticatable // Optional: implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id',
        'program_id',
        'class', // Consider renaming to class_year if 'class' is a PHP keyword issue for you
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'program_id' => 'integer',
        ];
    }

    /**
     * Get the leaves associated with the user (student).
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'student_id', 'id');
    }

    /**
     * Get the department that the user belongs to.
     */
    public function department(): BelongsTo // <-- UNCOMMENTED AND COMPLETED
    {
        // Assumes 'users' table has 'department_id' foreign key
        // and 'Department' model exists in App\Models namespace.
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Get the program that the user (student) belongs to.
     */
    public function program(): BelongsTo // NEW Relationship
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

}