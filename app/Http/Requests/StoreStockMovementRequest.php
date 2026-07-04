<?php

namespace App\Http\Requests;

use App\Enums\MovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreStockMovementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization handled by middleware
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                'exists:products,id'
            ],
            'movement_type' => [
                'required',
                'string',
                new Enum(MovementType::class)
            ],
            'quantity' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    // For adjustment, allow negative values
                    if ($this->input('movement_type') === 'adjustment') {
                        if ($value == 0) {
                            $fail('Adjustment quantity cannot be zero.');
                        }
                    } else {
                        if ($value < 1) {
                            $fail('Quantity must be at least 1.');
                        }
                    }
                },
            ],
            'reference_number' => [
                'required',
                'string',
                'max:255',
                'unique:stock_movements,reference_number'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Product is required.',
            'product_id.exists' => 'The selected product does not exist.',
            'movement_type.required' => 'Movement type is required.',
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be a number.',
            'reference_number.required' => 'Reference number is required.',
            'reference_number.unique' => 'This reference number has already been used.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'product',
            'movement_type' => 'movement type',
            'reference_number' => 'reference number',
        ];
    }
}