<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLibroRequest extends FormRequest
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
            'isbn' => ['required', 'string', 'regex:/^\d{13}$/', 'unique:libros,isbn'],
            'editorial_id' => ['required', 'exists:editoriales,id'],
            'categoria_id' => ['required', 'exists:categorias,id'],
        ];
    }
}
