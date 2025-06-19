<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePrestacionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'codigo' => [
                'required',
                'string',
                'size:6',
                'regex:/^\d{6}$/',
                Rule::unique('prestaciones')->ignore($this->prestacion)
            ],
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string|max:1000',
            'estado' => 'required|in:activo,inactivo,suspendido',
            'rubro_id' => 'required|exists:rubros,id',
            'precio_general' => 'required|numeric|min:0|max:999999.99',
            'valor_ips' => 'nullable|numeric|min:0|max:999999.99',
            'observaciones' => 'nullable|string|max:1000'
        ];
    }

    public function messages()
    {
        return [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.size' => 'El código debe tener exactamente 6 caracteres.',
            'codigo.regex' => 'El código debe contener solo números.',
            'codigo.unique' => 'Ya existe una prestación con este código.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 200 caracteres.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser activo, inactivo o suspendido.',
            'rubro_id.required' => 'Debe seleccionar un rubro.',
            'rubro_id.exists' => 'El rubro seleccionado no existe.',
            'precio_general.required' => 'El precio general es obligatorio.',
            'precio_general.numeric' => 'El precio general debe ser un número.',
            'precio_general.min' => 'El precio general no puede ser negativo.',
            'precio_general.max' => 'El precio general no puede superar los $999,999.99.',
            'valor_ips.numeric' => 'El valor IPS debe ser un número.',
            'valor_ips.min' => 'El valor IPS no puede ser negativo.',
            'valor_ips.max' => 'El valor IPS no puede superar los $999,999.99.'
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('codigo')) {
            $this->merge([
                'codigo' => preg_replace('/\D/', '', $this->codigo)
            ]);
        }
    }
}
