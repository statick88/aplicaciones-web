<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLectorRequest extends FormRequest
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
            'cedula' => [
                'required', 'string', 'regex:/^\d{10}$/',
                Rule::unique('lectores', 'cedula')->ignore($this->route('lector')),
            ],
            'correo' => [
                'required', 'email', 'max:160',
                Rule::unique('lectores', 'correo')->ignore($this->route('lector')),
            ],
            'telefono' => ['required', 'string', 'regex:/^\d{7,15}$/'],
            'fecha_registro' => ['required', 'date'],
        ];
    }
}
