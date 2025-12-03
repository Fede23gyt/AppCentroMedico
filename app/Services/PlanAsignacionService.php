<?php

namespace App\Services;

use App\Models\Afiliado;
use App\Models\AfiliadoItemHistorico;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlanAsignacionService
{
    /**
     * Procesar items del afiliado y asignar plan correspondiente
     *
     * @param Afiliado $afiliado
     * @param array $items Array de códigos de items (ej: ['NPIEVE', 'NIVELX7', 'PRESTA0'])
     * @param string $certificado
     * @return array ['success' => bool, 'plan' => Plan|null, 'message' => string]
     */
    public function procesarYAsignarPlan(Afiliado $afiliado, array $items, string $certificado): array
    {
        try {
            DB::beginTransaction();

            // 1. Guardar histórico de items
            $this->guardarHistoricoItems($afiliado->id, $items, $certificado);

            // 2. Actualizar items actuales del afiliado
            $afiliado->update([
                'items_actuales' => $items,
                'ultima_verificacion_items' => now(),
            ]);

            // 3. Buscar plan que coincida con estos items
            $plan = $this->buscarPlanPorItems($items);

            if ($plan) {
                // Plan encontrado
                $afiliado->update([
                    'plan_id' => $plan->id,
                    'tiene_cobertura' => true,
                ]);

                DB::commit();

                Log::info('Plan asignado exitosamente', [
                    'afiliado_id' => $afiliado->id,
                    'plan_id' => $plan->id,
                    'items' => $items,
                ]);

                return [
                    'success' => true,
                    'plan' => $plan,
                    'message' => "Plan '{$plan->nombre}' asignado correctamente",
                ];
            } else {
                // No se encontró plan
                $afiliado->update([
                    'plan_id' => null,
                    'tiene_cobertura' => false,
                ]);

                DB::commit();

                Log::warning('No se encontró plan para los items', [
                    'afiliado_id' => $afiliado->id,
                    'items' => $items,
                ]);

                return [
                    'success' => false,
                    'plan' => null,
                    'message' => 'No se encontró un plan que coincida con los items del afiliado',
                ];
            }

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al procesar items y asignar plan', [
                'afiliado_id' => $afiliado->id,
                'items' => $items,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'plan' => null,
                'message' => 'Error al procesar: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Buscar plan que coincida con los items proporcionados
     * Ahora soporta múltiples combinaciones de items por plan
     *
     * @param array $itemsAfiliado
     * @return Plan|null
     */
    public function buscarPlanPorItems(array $itemsAfiliado): ?Plan
    {
        // Obtener todos los planes activos con sus items requeridos
        $planes = Plan::with('itemsRequeridos')
            ->where('estado', 'activo')
            ->get();

        $planesCoincidentes = [];

        foreach ($planes as $plan) {
            // Agrupar items del plan por su grupo/combinación
            $combinaciones = $plan->itemsRequeridos->groupBy('grupo');

            // Verificar cada combinación
            foreach ($combinaciones as $nombreGrupo => $itemsGrupo) {
                $itemsRequeridos = $itemsGrupo->pluck('item_codigo')->toArray();

                // Verificar si el afiliado tiene TODOS los items de esta combinación
                $tieneTodasLasClaves = empty(array_diff($itemsRequeridos, $itemsAfiliado));

                if ($tieneTodasLasClaves && count($itemsRequeridos) > 0) {
                    $planesCoincidentes[] = [
                        'plan' => $plan,
                        'grupo' => $nombreGrupo,
                        'items_count' => count($itemsRequeridos),
                        'es_exacto' => count($itemsRequeridos) === count($itemsAfiliado),
                        'descripcion_grupo' => $itemsGrupo->first()->descripcion_grupo ?? null,
                    ];

                    // Si ya encontramos una combinación para este plan, no necesitamos seguir buscando
                    // (un plan solo se puede asignar una vez)
                    break;
                }
            }
        }

        if (empty($planesCoincidentes)) {
            return null;
        }

        // Priorizar selección:
        // 1. Coincidencia exacta (mismo número de items)
        // 2. Plan con mayor cantidad de items requeridos (más específico)
        usort($planesCoincidentes, function ($a, $b) {
            // Primero, preferir coincidencias exactas
            if ($a['es_exacto'] && !$b['es_exacto']) {
                return -1;
            }
            if (!$a['es_exacto'] && $b['es_exacto']) {
                return 1;
            }

            // Si ambos son exactos o ninguno, preferir el que tenga más items (más específico)
            return $b['items_count'] - $a['items_count'];
        });

        // Log de la combinación que coincidió
        $mejorCoincidencia = $planesCoincidentes[0];
        Log::info('Plan encontrado con combinación específica', [
            'plan' => $mejorCoincidencia['plan']->nombre,
            'grupo' => $mejorCoincidencia['grupo'],
            'descripcion_grupo' => $mejorCoincidencia['descripcion_grupo'],
            'items_count' => $mejorCoincidencia['items_count'],
        ]);

        return $mejorCoincidencia['plan'];
    }

    /**
     * Guardar histórico de items del afiliado
     *
     * @param int $afiliadoId
     * @param array $items
     * @param string $certificado
     * @return void
     */
    private function guardarHistoricoItems(int $afiliadoId, array $items, string $certificado): void
    {
        foreach ($items as $item) {
            AfiliadoItemHistorico::create([
                'afiliado_id' => $afiliadoId,
                'item_codigo' => $item,
                'fecha_lectura' => now(),
                'certificado' => $certificado,
            ]);
        }
    }

    /**
     * Obtener items del afiliado desde la BD externa
     * (Este método debe implementarse según la estructura de tu BD externa)
     *
     * @param string $certificado
     * @return array
     */
    public function obtenerItemsDesdeBDExterna(string $certificado): array
    {
        // TODO: Implementar lectura desde BD externa MSSQL
        // Ejemplo:
        // $items = DB::connection('sqlsrv_external')
        //     ->table('tabla_items')
        //    ->where('certificado', $certificado)
        //     ->pluck('item_codigo')
        //     ->toArray();
        //
        // return $items;

        return [];
    }
}
