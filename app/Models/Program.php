<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// You might also need to import StudentClass if not already auto-loaded or if you add type hints
// use App\Models\StudentClass;
// use Illuminate\Database\Eloquent\Relations\HasMany; // For return type hinting
// use Illuminate\Database\Eloquent\Relations\BelongsTo; // For return type hinting

class Program extends Model
{
    use HasFactory;
    protected $fillable = ['department_id', 'name', 'code'];

    public function department() // : BelongsTo // Optional type hint
    {
        return $this->belongsTo(Department::class);
    }

    public function studentClasses() // : HasMany // Optional type hint
    {
        return $this->hasMany(StudentClass::class); // Ensure StudentClass model exists and is correctly named
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName() // <--- THIS IS THE MISSING PIECE
    {
        return 'code'; // Tells Laravel to use the 'code' column for route model binding
    }
}