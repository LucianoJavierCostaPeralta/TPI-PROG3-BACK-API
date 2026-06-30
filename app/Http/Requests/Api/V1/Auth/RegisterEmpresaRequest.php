<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'razon_social' => ['required', 'string', 'min:2', 'max:255'],
            'cuit' => ['required', 'string', "regex:/^\d{11}$/", 'unique:empresas,cuit'],
            'nombre_completo' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:empresas,email_contacto'],
            'password' => ['required', 'string', 'min:6'],
            'telefono' => ['required', 'string', 'max:50'],
            'tamano_flota' => ['required', Rule::in(['1-10', '11-30', '31-100', 'Más de 100'])],
            'terminos_aceptados' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'cuit.regex' => 'El CUIT debe contener exactamente 11 digitos.',
            'terminos_aceptados.accepted' => 'Debes aceptar los terminos y condiciones.',
        ];
    }
}
