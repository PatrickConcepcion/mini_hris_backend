<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add authorization logic if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'personal_email' => ['sometimes', 'required', 'email', 'unique:employees,' . $this->route('employee')->id],
            'employee_no' => ['nullable', 'string', 'unique:employees,' . $this->route('employee')->id, 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'date_hired' => ['nullable', 'date'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'country' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'in:male,female,other'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:255'],
            'height_cm' => ['nullable', 'numeric', 'min:50', 'max:250'],
            'weight_kg' => ['nullable', 'numeric', 'min:20', 'max:300'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'personal_email.unique' => 'This email address is already in use.',
            'employee_no.unique' => 'This employee number is already in use.',
            'date_of_birth.before' => 'Date of birth must be before today.',
        ];
    }
}