<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClass extends Model
{
    use HasFactory;
    protected $fillable = ['program_id', 'name', 'code'];
    protected $table = 'student_classes'; // Explicitly define table name

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}