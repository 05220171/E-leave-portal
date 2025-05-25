<?php

namespace App\Http\Requests;

use App\Models\User; // Make sure you have this use statement for User model
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // For Rule::in, Rule::unique, etc.

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Typically, you'd check if the authenticated user has permission
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = $this->route('user'); // Get the user model instance being updated

        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)], // Ignore current user's email
            'role' => ['required', 'string', Rule::in(['admin', 'student', 'hod', 'dsa', 'sso', 'superadmin'])],
            'department_id' => 'required|exists:departments,id',
        ];

        // Password is optional on update
        if ($this->filled('password')) {
            $rules['password'] = 'sometimes|string|min:8|confirmed';
        }

        // Add rules specific to 'student' role
        if ($this->input('role') === 'student') {
            $rules['program'] = 'required|string|max:255';
            $rules['class'] = 'required|string|max:255'; // Assuming 'class' is the field name
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->route('user'); // Get the user model instance being updated
            $role = $this->input('role');
            $departmentId = $this->input('department_id');

            // 1. Validation: One HOD per department_id
            if ($role === 'hod') {
                if (empty($departmentId)) {
                    $validator->errors()->add('department_id', 'A department must be selected for an HOD.');
                } else {
                    $existingHod = User::where('role', 'hod')
                                       ->where('department_id', $departmentId)
                                       ->where('id', '!=', $user->id) // Exclude the current user being edited
                                       ->first();
                    if ($existingHod) {
                        $validator->errors()->add('role', 'Another user is already the HOD for the selected department. Please choose a different department or role.');
                    }
                }
            }

            // 2. Validation: Only one DSA in the entire system
            if ($role === 'dsa') {
                $existingDsa = User::where('role', 'dsa')
                                   ->where('id', '!=', $user->id) // Exclude the current user being edited
                                   ->first();
                if ($existingDsa) {
                    $validator->errors()->add('role', 'Another user is already the DSA. Only one DSA is permitted.');
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'department_id.required' => 'The department field is required.',
            'department_id.exists' => 'The selected department is invalid.',
            'program.required' => 'The program field is required for students.',
            'class.required' => 'The class field is required for students.',
            // Add other custom messages as needed
        ];
    }
}