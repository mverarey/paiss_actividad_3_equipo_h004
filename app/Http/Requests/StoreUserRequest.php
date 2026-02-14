<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    // ✅ PREVENCIÓN: Validación estricta
    public function rules()
    {
        return [
            'email' => [
                'required',
                'email',                    // ✅ Formato válido
                'unique:users,email',       // ✅ Previene duplicados
                'max:255'
            ],
            'password' => [
                'required',
                'confirmed',                // ✅ Debe coincidir con confirmación
                Password::min(8)           // ✅ Requisitos de seguridad
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'birth_date' => [
                'required',
                'date',
                'before:today',            // ✅ No puede ser futuro
                'after:1900-01-01'         // ✅ Fecha razonable
            ],
            'phone' => [
                'nullable',
                'regex:/^[0-9]{10}$/'      // ✅ Formato específico
            ]
        ];
    }
}