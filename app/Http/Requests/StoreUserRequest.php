<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Program; // <--- ADD THIS: Import the Program model
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // For Rule::in, Rule::exists etc.

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Assuming superadmin can always create users
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', 'string', Rule::in(['admin', 'student', 'hod', 'dsa', 'sso', 'superadmin'])],
            'department_id' => 'required|integer|exists:departments,id', // Ensure it's an integer and exists
        ];

        // Add rules specific to 'student' role for program and class
        if ($this->input('role') === 'student') {
            $rules['program'] = [
                'required', // The program code is required
                'string',   // It should be a string (the program code)
                Rule::exists('programs', 'code')->where(function ($query) {
                    // This closure ensures the selected program 'code'
                    // actually belongs to the selected 'department_id'.
                    $query->where('department_id', $this->input('department_id'));
                }),
            ];

            $rules['class'] = [
                'required', // The class code is required
                'string',   // It should be a string (the class code)
                Rule::exists('student_classes', 'code')->where(function ($query) {
                    // This closure ensures the selected class 'code'
                    // actually belongs to the selected 'program' (identified by its code).
                    $programCode = $this->input('program');
                    if ($programCode) {
                        // Find the program model by its code to get its ID
                        $program = Program::where('code', $programCode)
                                          ->where('department_id', $this->input('department_id')) // Double-check program is in selected dept
                                          ->first();
                        if ($program) {
                            // Now validate that the class code exists for this program_id
                            $query->where('program_id', $program->id);
                        } else {
                            // If the program code submitted isn't valid or doesn't belong to the department,
                            // this class validation should fail. Forcing failure.
                            $query->whereRaw('1 = 0'); // This condition will never be true
                        }
                    } else {
                        // If no program code was submitted (though 'program' is required),
                        // this class validation should also fail.
                        $query->whereRaw('1 = 0');
                    }
                }),
            ];
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     * (HOD and DSA validation remains the same)
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $role = $this->input('role');
            $departmentId = $this->input('department_id');

            // 1. Validation: One HOD per department_id
            if ($role === 'hod') {
                if (empty($departmentId)) {
                    $validator->errors()->add('department_id', 'A department must be selected for an HOD.');
                } else {
                    $existingHod = User::where('role', 'hod')
                                       ->where('department_id', $departmentId)
                                       ->first();
                    if ($existingHod) {
                        $validator->errors()->add('role', 'An HOD already exists for the selected department. Please choose a different department or role.');
                    }
                }
            }

            // 2. Validation: Only one DSA in the entire system
            if ($role === 'dsa') {
                $existingDsa = User::where('role', 'dsa')->first();
                if ($existingDsa) {
                    $validator->errors()->add('role', 'A DSA already exists in the system. Only one DSA is permitted.');
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'department_id.required' => 'The department field is required.',
            'department_id.exists' => 'The selected department is invalid.',
            'program.required' => 'The program field is required for students.',
            'program.exists' => 'The selected program is invalid or does not belong to the chosen department.',
            'class.required' => 'The class field is required for students.',
            'class.exists' => 'The selected class is invalid or does not belong to the chosen program.',
            // Add other custom messages as needed
        ];
    }
}