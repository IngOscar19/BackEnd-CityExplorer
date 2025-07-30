<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'correo' => 'required|email|exists:Usuario,correo' 
        ];
    }

    public function messages()
    {
        return [
            'correo.required' => 'El correo es obligatorio',
            'correo.email' => 'Formato de correo inválido',
            'correo.exists' => 'Este correo no está registrado'
        ];
    }
}