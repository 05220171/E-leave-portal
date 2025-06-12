<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Program; // Keep for now
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userToUpdate = $this->route('user'); // Get the User model instance being updated

        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userToUpdate->id) // Ignore current user
            ],
            'password' => 'nullable|string|min:8|confirmed', // Password is optional on update
            'role' => ['required', 'string', Rule::in(['admin', 'student', 'hod', 'dsa', 'sso', 'superadmin', 'daa', 'president'])],
            'department_id' => 'nullable|integer|exists:departments,id',
        ];

        if ($this->input('role') === 'student' || $this->input('role') === 'hod') {
            $rules['department_id'] = 'required|integer|exists:departments,id';
        }

        if ($this->input('role') === 'student') {
            $rules['program_id'] = [
                'required',
                'integer',
                Rule::exists('programs', 'id')->where(function ($query) {
                    $query->where('department_id', $this->input('department_id'));
                }),
            ];
            $rules['class'] = 'required|string|max:255';
        } else {
            $rules['program_id'] = 'nullable|integer|exists:programs,id';
            $rules['class'] = 'nullable|string|max:255';
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $userToUpdate = $this->route('user');
            $role = $this->input('role');
            $departmentId = $this->input('department_id');

            if ($role === 'hod') {
                if (empty($departmentId)) {
                    $validator->errors()->add('department_id', 'A department must be selected for an HOD.');
                } else {
                    $existingHod = User::where('role', 'hod')
                                       ->where('department_id', $departmentId)
                                       ->where('id', '!=', $userToUpdate->id) // Exclude current user
                                       ->exists();
                    if ($existingHod) {
                        $validator->errors()->add('role', 'Another user is already the HOD for this department.');
                    }
                }
            }

            $singularSystemRoles = ['dsa', 'president', 'sso', 'daa'];
            if (in_array($role, $singularSystemRoles)) {
                $existingSingularRoleUser = User::where('role', $role)
                                             ->where('id', '!=', $userToUpdate->id) // Exclude current user
                                             ->exists();
                if ($existingSingularRoleUser) {
                    $validator->errors()->add('role', 'Another user already holds the role ' . Str::upper($role) . '. This role must be unique.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'department_id.required' => 'The department field is required when role is Student or HOD.',
            'department_id.exists' => 'The selected department is invalid.',
            'program_id.required' => 'The program field is required for students.',
            'program_id.exists' => 'The selected program is invalid or does not belong to the chosen department.',
            'class.required' => 'The class field is required for students.',
        ];
    }
}