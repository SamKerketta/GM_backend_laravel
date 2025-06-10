<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:100',
            'dob'               => 'required|date',
            'gender'            => 'required|in:male,female,other', // Fixed syntax
            'email'             => 'required|email|max:100|unique:members,email',
            'phone'             => 'required|string|max:15|unique:members,phone',
            'address'           => 'required|string|max:200',
            'membershipStart'   => 'required|date',
            'membershipEnd'     => 'nullable|date|after_or_equal:membership_start',
            'isPayment'         => 'required',
            'planId'            => 'required_if:isPayment,1|integer',
            'shiftId'           => 'required|in:1,2,3',
            'assignedTrainer'   => 'nullable|integer',
            'photo'             => 'nullable|image|mimes:jpg,png', // or 'image|mimes:jpg,png' if it's a file upload
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'message' => 'Validation Error',
            'errors'  => $validator->errors()
        ], 422));
    }
}
