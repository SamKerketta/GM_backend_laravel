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
            'email'             => 'required|email|max:100',
            'phone'             => 'required|string|max:15',
            'membershipStart'   => 'required|date',
            'membershipEnd'     => 'nullable|date|after_or_equal:membership_start',
            'planId'            => 'required|integer',
            'assignedTrainer'   => 'nullable|integer',
            'photo'             => 'nullable|string|max:255', // or 'image|mimes:jpg,png' if it's a file upload
            'created_at'        => 'nullable|date',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'=>false,
            'message' => 'Validation Error',
            'errors'  => $validator->errors()
        ], 200));
    }
}
