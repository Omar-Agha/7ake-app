<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_items'         => ['required', 'array', 'min:1'],
            'address_id' => ['required', 'exists:customer_address,id'],
            'payment_method' => ['required', 'integer', 'in:0,1,2'],

            'order_items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'order_items.*.quantity'          => ['required', 'integer', 'min:1'],


        ];
    }
}
