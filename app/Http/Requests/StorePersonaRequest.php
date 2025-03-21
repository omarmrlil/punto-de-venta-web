<?php

namespace App\Http\Requests;

use App\Enums\TipoPersonaEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePersonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    return [
        'razon_social' => 'required|max:255',
        'direccion' => 'nullable|max:255',
        'telefono' => 'nullable|max:15',
        'tipo' => ['required', new Enum(TipoPersonaEnum::class)],
        'email' => 'nullable|email|max:255',
        'estado' => 'nullable|integer|min:0|max:1',
        'documento_id' => 'required|integer|exists:documentos,id',
        'numero_documento' => 'required|max:20|unique:personas,numero_documento',
    ];
}

    public function messages(): array
    {
        return [
            'razon_social.required' => 'El campo razón social es obligatorio.',
            'razon_social.max' => 'El campo razón social no debe exceder los 255 caracteres.',
            'direccion.max' => 'El campo dirección no debe exceder los 255 caracteres.',
            'telefono.max' => 'El campo teléfono no debe exceder los 15 caracteres.',
            'tipo.required' => 'El campo tipo es obligatorio.',
            'tipo.in' => 'El campo tipo debe ser NATURAL o JURIDICA.',
            'email.email' => 'El campo email debe tener un formato válido.',
            'email.max' => 'El campo email no debe exceder los 255 caracteres.',
            'estado.integer' => 'El campo estado debe ser un número entero.',
            'estado.min' => 'El campo estado debe ser 0 o 1.',
            'estado.max' => 'El campo estado debe ser 0 o 1.',
            'documento_id.required' => 'El campo documento ID es obligatorio.',
            'documento_id.exists' => 'El documento seleccionado no existe.',
            'numero_documento.required' => 'El campo número de documento es obligatorio.',
            'numero_documento.max' => 'El campo número de documento no debe exceder los 20 caracteres.',
            'numero_documento.unique' => 'El número de documento ya está registrado.',
        ];
    }
}
