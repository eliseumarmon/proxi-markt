<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PuntosEntregaRequest extends FormRequest
{
    public function rules()
    {
        return [
            'nombre_punto'    => 'required|string|max:255',
            'direccion_punto' => 'nullable|string|max:255',
            'longitud'        => 'required|numeric',
            'latitud'         => 'required|numeric',
        ];
    }
}
