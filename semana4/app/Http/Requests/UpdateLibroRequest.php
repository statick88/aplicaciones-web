<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLibroRequest extends FormRequest
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
            'titulo' => ['required', 'string', 'max:200'],
            'anio' => ['required', 'integer', 'min:1000', 'max:2026'],
            'isbn' => [
                'required', 'string', 'regex:/^\d{13}$/',
                Rule::unique('libros', 'isbn')->ignore($this->route('libro')),
            ],
            'editorial_id' => ['required', 'exists:editoriales,id'],
            'categoria_id' => ['required', 'exists:categorias,id'],
        ];
    }
}
