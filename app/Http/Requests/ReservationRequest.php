<?php

namespace App\Http\Requests;

use App\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }

    protected function passedValidation(): void
    {
        $product = $this->route('product');
        $quantity = (int) $this->validated('quantity');

        if ($product->status !== ProductStatus::Available) {
            throw ValidationException::withMessages([
                'quantity' => 'This product is not available for reservation.',
            ]);
        }

        if ($quantity > $product->availableQuantity()) {
            throw ValidationException::withMessages([
                'quantity' => 'Requested quantity exceeds available stock.',
            ]);
        }
    }
}
