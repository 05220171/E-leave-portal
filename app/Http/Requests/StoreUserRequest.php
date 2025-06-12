<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Program; // Keep this for now, though direct use might be removed
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\ValidJnecUserEmail;
use Illuminate\Support\Str; // For Str::upper

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string', // Ensure it's a string
                'email',
                'max:255', // Max length for email
                Rule::unique('users', 'email'), // Using Rule::unique for clarity
                new ValidJnecUserEmail,
            ],
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', 'string', Rule::in(['admin', 'student', 'hod', 'dsa', 'sso', 'superadmin', 'daa', 'president'])],
            // department_id is nullable by default, made required conditionally below
            'department_id' => 'nullable|integer|exists:departments,id',
        ];

        // Conditional requirement for department_id
        if ($this->input('role') === 'student' || $this->input('role') === 'hod') {
            $rules['department_id'] = 'required|integer|exists:departments,id';
        }

        // Conditional requirement for student fields
        if ($this->input('role') === 'student') {
            // Program is now program_id and selected from a dropdown based on department
            $rules['program_id'] = [
                'required',
                'integer',
                Rule::exists('programs', 'id')->where(function ($query) {
                    // Ensure the selected program_id actually belongs to the selected department_id
                    $query->where('department_id', $this->input('department_id'));
                }),
            ];
            // Assuming 'class' is still a string. If it becomes a dropdown from a 'student_classes' table:
            // $rules['class_id'] = ['required', 'integer', Rule::exists('student_classes', 'id')->where(...)];
            $rules['class'] = 'required|string|max:255';
        } else {
            // For non-students, program_id and class should be nullable or not present
            $rules['program_id'] = 'nullable|integer|exists:programs,id';
            $rules['class'] = 'nullable|string|max:255';
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $role = $this->input('role');
            $departmentId = $this->input('department_id');

            if ($role === 'hod') {
                if (empty($departmentId)) { // This should ideally be caught by the 'required' rule above
                    $validator->errors()->add('department_id', 'A department must be selected for an HOD.');
                } else {
                    $existingHod = User::where('role', 'hod')
                                       ->where('department_id', $departmentId)
                                       ->exists(); // More efficient
                    if ($existingHod) {
                        $validator->errors()->add('role', 'An HOD already exists for the selected department.');
                    }
                }
            }

            // Consolidate singular role check
            $singularSystemRoles = ['dsa', 'president', 'sso', 'daa']; // Add 'admin', 'superadmin' if they are also strictly singular
            if (in_array($role, $singularSystemRoles)) {
                $existingSingularRoleUser = User::where('role', $role)->exists();
                if ($existingSingularRoleUser) {
                    $validator->errors()->add('role', 'A user with the role ' . Str::upper($role) . ' already exists. This role must be unique.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'department_id.required' => 'The department field is required when role is Student or HOD.',
            'department_id.exists' => 'The selected department is invalid.',
            'program_id.required' => 'The program field is required for students.', // Changed from program.required
            'program_id.exists' => 'The selected program is invalid or does not belong to the chosen department.', // Changed from program.exists
            'class.required' => 'The class field is required for students.',
            // 'class.exists' => 'The selected class is invalid or does not belong to the chosen program.', // Only if class becomes an ID
        ];
    }
}