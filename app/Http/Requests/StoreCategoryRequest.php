<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_name' => ['required', 'string', 'max:255', 'unique:categories,category_name'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_name.required' => 'The category name is required.',
            'category_name.unique' => 'This category already exists.',
        ];
    }
}