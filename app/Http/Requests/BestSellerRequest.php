<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BestSellerRequest extends FormRequest
{
    public function authorize(): bool
    {
        // todo: skipping authorization
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'offset' => 'integer|multiple_of:20' // todo: instead of propagating constraints, consider adjusting values
        ];
    }
}
