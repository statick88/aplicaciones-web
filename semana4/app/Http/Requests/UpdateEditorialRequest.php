<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEditorialRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'pais' => ['required', 'string', 'max:60'],
            'pagina_web' => ['nullable', 'string', 'max:120', 'url'],
            'fundacion' => ['nullable', 'integer', 'min:1000', 'max:2026'],
            'activa' => ['sometimes', 'boolean'],
        ];
    }
}
