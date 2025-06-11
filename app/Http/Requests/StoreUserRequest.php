<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Program;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\ValidJnecUserEmail; // <-- 1. IMPORT YOUR CUSTOM RULE

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
            
            // 2. MODIFIED THIS SECTION TO INCLUDE YOUR CUSTOM RULE
            'email' => [
                'required',
                'email',
                'unique:users,email',
                new ValidJnecUserEmail, // <-- Your custom rule is now active here
            ],

            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', 'string', Rule::in(['admin', 'student', 'hod', 'dsa', 'sso', 'superadmin'])],
            'department_id' => 'required|integer|exists:departments,id',
        ];

        // This part remains unchanged
        if ($this->input('role') === 'student') {
            $rules['program'] = [
                'required',
                'string',
                Rule::exists('programs', 'code')->where(function ($query) {
                    $query->where('department_id', $this->input('department_id'));
                }),
            ];

            $rules['class'] = [
                'required',
                'string',
                Rule::exists('student_classes', 'code')->where(function ($query) {
                    $programCode = $this->input('program');
                    if ($programCode) {
                        $program = Program::where('code', $programCode)
                                          ->where('department_id', $this->input('department_id'))
                                          ->first();
                        if ($program) {
                            $query->where('program_id', $program->id);
                        } else {
                            $query->whereRaw('1 = 0');
                        }
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                }),
            ];
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     * This part remains unchanged
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $role = $this->input('role');
            $departmentId = $this->input('department_id');

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
     * This part remains unchanged
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
        ];
    }
}