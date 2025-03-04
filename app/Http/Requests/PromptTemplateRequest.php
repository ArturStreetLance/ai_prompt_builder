<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromptTemplateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'version' => 'required|string',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id'
        ];
    }
} 