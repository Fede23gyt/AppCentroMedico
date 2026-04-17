<?php

namespace App\Observers;

use App\Models\Prestacion;
use App\Models\HistorialPrecioPrestacion;
use Illuminate\Support\Facades\Auth;

class PrestacionObserver
{
    /**
     * Al actualizar una prestación, detectar cambios en precio_1..precio_4
     * y registrar el historial automáticamente.
     */
    public function updating(Prestacion $prestacion): void
    {
        $niveles = [1, 2, 3, 4];
        $hoy = now()->toDateString();
        $userId = Auth::id();

        foreach ($niveles as $nivel) {
            $campo = "precio_{$nivel}";

            $valorAnterior = (float) $prestacion->getOriginal($campo);
            $valorNuevo    = (float) $prestacion->{$campo};

            if ($valorAnterior === $valorNuevo) {
                continue; // Sin cambio, no registrar
            }

            // Cerrar el registro vigente anterior (fecha_vigencia_hasta)
            HistorialPrecioPrestacion::where('prestacion_id', $prestacion->id)
                ->where('nivel_precio', $nivel)
                ->whereNull('fecha_vigencia_hasta')
                ->update(['fecha_vigencia_hasta' => $hoy]);

            // Crear nuevo registro de historial
            HistorialPrecioPrestacion::create([
                'prestacion_id'        => $prestacion->id,
                'nivel_precio'         => $nivel,
                'valor_anterior'       => $valorAnterior,
                'valor_nuevo'          => $valorNuevo,
                'fecha_vigencia_desde' => $hoy,
                'fecha_vigencia_hasta' => null, // Vigente
                'user_id'              => $userId,
                'motivo'               => request('motivo_cambio_precio'),
            ]);
        }
    }
}
