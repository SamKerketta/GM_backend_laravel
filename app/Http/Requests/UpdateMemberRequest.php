<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class UpdateMemberRequest extends FormRequest
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
            'id'                => 'required|integer',
            'name'              => 'required|string|max:100',
            'email'             => 'nullable|email|max:100',
            'dob'               => 'nullable|date',
            'gender'            => 'required|in:male,female,other', // Fixed syntax
            'phone'             => 'required|string|max:15',
            'address'           => 'required|string|max:200',
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
