<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
// REMOVE SkipsOnError and Throwable if you want exceptions to propagate
// use Maatwebsite\Excel\Concerns\SkipsOnError;
// use Throwable;

class UsersImport implements
    ToModel,
    WithHeadingRow,
    WithValidation // REMOVE SkipsOnError from this line
{
    private $allowedRoles = ['student', 'hod', 'admin', 'dsa', 'sso', 'superadmin'];

    public function model(array $row)
    {
        // ... (your model logic remains the same)
        $role = 'student';
        if (!empty($row['role'])) {
            $potentialRole = strtolower(trim($row['role']));
            if (in_array($potentialRole, $this->allowedRoles)) {
                $role = $potentialRole;
            } else {
                Log::warning("Invalid role '{$row['role']}' for user {$row['email']}. Defaulting to 'student'.");
            }
        } else {
            Log::warning("Missing role for user {$row['email']}. Defaulting to 'student'.");
        }

        $departmentId = null;
        if (isset($row['department_id']) && is_numeric($row['department_id'])) {
            $departmentId = (int) $row['department_id'];
        }

        return new User([
            'name'     => trim($row['name']),
            'email'    => trim($row['email']),
            'password' => Hash::make($row['password'] ?? 'password123'),
            'role'     => $role,
            'department_id' => $departmentId,
            'program'  => ($role === 'student' && isset($row['program'])) ? trim($row['program']) : null,
            'class'    => ($role === 'student' && isset($row['class'])) ? trim($row['class']) : null,
            'email_verified_at' => now(),
        ]);
    }

    public function rules(): array
    {
        // Your rules are correct for unique email check
        return [
            'name'      => ['required', 'string', 'max:255'],
            '*.email'   => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'email'     => ['required', 'email', 'max:255'],
            'password'  => ['nullable', 'string', 'min:8'],
            'role'      => ['nullable', 'string', Rule::in($this->allowedRoles)],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'program'   => ['nullable', 'string', 'max:255', 'required_if:role,student'],
            'class'     => ['nullable', 'string', 'max:255', 'required_if:role,student'],
        ];
    }

    public function customValidationMessages(): array
    {
        // Your custom messages are fine
        return [
            '*.email.unique' => 'The email :input already exists (either in DB or this file).',
            'email.required' => 'Email is required.',
            'name.required' => 'Name is required.',
            // ... other messages
        ];
    }

    // REMOVE the onError method if you remove SkipsOnError
    // public function onError(Throwable $e)
    // {
    //     Log::error("Error during Excel import row processing: " . $e->getMessage(), ['exception' => $e]);
    // }
}