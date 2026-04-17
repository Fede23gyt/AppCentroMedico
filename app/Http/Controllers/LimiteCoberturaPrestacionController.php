<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LimiteCoberturaPrestacion;
use Illuminate\Validation\Rule;

class LimiteCoberturaPrestacionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prestacion_id' => 'required|exists:prestaciones,id',
            'cobertura_codigo' => [
                'nullable',
                Rule::unique('limites_cobertura_prestaciones')->where(function ($query) use ($request) {
                    return $query->where('prestacion_id', $request->prestacion_id);
                })
            ],
            'limite_mensual_individual' => 'required|integer|min:0',
            'limite_mensual_familia' => 'required|integer|min:0',
        ], [
            'cobertura_codigo.unique' => 'Ya existe un tope configurado para esta cobertura en esta prestación.'
        ]);

        LimiteCoberturaPrestacion::create($validated);

        return back()->with('success', 'Tope configurado correctamente.');
    }

    public function destroy(LimiteCoberturaPrestacion $limiteCobertura)
    {
        $limiteCobertura->delete();
        return back()->with('success', 'Tope eliminado correctamente.');
    }
}
