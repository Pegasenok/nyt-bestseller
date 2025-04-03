<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Intervention\Validation\Rules\Isbn;

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
            'offset' => 'integer|multiple_of:20', // todo: instead of propagating constraints, consider adjusting values
            'isbn' => 'array',
            'isbn.*' => new Isbn(),
            'title' => 'string',
            'author' => 'string',
        ];
    }

    protected function prepareForValidation()
    {
        // accept isbn parameter both as string and array
        if (isset($this->isbn) && is_string($this->isbn)) {
            $this->merge([
                'isbn' => [$this->isbn],
            ]);
        }
    }
}
