<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePrestamoRequest extends FormRequest
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
            'fecha_prestamo' => ['required', 'date'],
            'fecha_devolucion' => ['nullable', 'date', 'after_or_equal:fecha_prestamo'],
            'estado' => ['required', Rule::in(['activo', 'devuelto'])],
            'lector_id' => ['required', 'exists:lectores,id'],
            'libro_id' => ['required', 'exists:libros,id'],
        ];
    }
}
