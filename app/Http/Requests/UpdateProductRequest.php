<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|min:2',
            'quantity' => 'required|integer|min:1|max:999999',
            'price' => 'required|numeric|min:0.01|max:999999.99'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required',
            'name.min' => 'Product name must be at least 2 characters',
            'quantity.required' => 'Quantity is required',
            'quantity.min' => 'Quantity must be at least 1',
            'quantity.max' => 'Quantity cannot exceed 999,999',
            'price.required' => 'Price is required',
            'price.min' => 'Price must be at least $0.01',
            'price.max' => 'Price cannot exceed $999,999.99'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
