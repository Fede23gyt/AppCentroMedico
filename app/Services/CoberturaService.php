<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CoberturaService
{
    protected $mapeoCoberturas = [];

    public function __construct()
    {
        // Pre-cargar el mapeo de la tabla de SQL Server como lo hacía el comando
        try {
            $this->mapeoCoberturas = DB::connection('sqlsrv_externa')
                ->table('tb_Tabla_Cobertura')
                ->select('iIdOrigenTabla', 'sIdTabla', 'sCobertura', 'iIdRubro')
                ->get()
                ->groupBy('iIdOrigenTabla');
        } catch (\Exception $e) {
            \Log::error("CoberturaService: No se pudo conectar a tb_Tabla_Cobertura: " . $e->getMessage());
            $this->mapeoCoberturas = collect();
        }
    }

    /**
     * Infiere la cobertura conectándose en tiempo real a PieveSalud
     */
    public function inferirCoberturaTitular($idTitularCF)
    {
        // 1. Obtener datos de Ficha (localidad para la regla regional)
        $ficha = DB::connection('sqlsrv_externa')
            ->table('Fichas')
            ->where('IdTitularCp', $idTitularCF)
            ->where('Existe', 1)
            ->first();

        $localidadId = $ficha ? $ficha->IdLocalidadAltaCf : null;

        // 2. Obtener planes y adicionales
        $planes = DB::connection('sqlsrv_externa')
            ->table('PlanesFichas')
            ->where('IdTitularCF', $idTitularCF)
            ->get();

        $adicionales = DB::connection('sqlsrv_externa')
            ->table('AdicionalPlanFicha')
            ->where('IdTitularCf', $idTitularCF)
            ->get();

        // 3. Aplicar lógica de negocio
        return $this->determinarCoberturasProceso($planes, $adicionales, $localidadId);
    }

    private function determinarCoberturasProceso($planes, $adicionales, $localidadId)
    {
        $coberturas = [];

        // 1. Mapeo dinámico por Adicionales
        if ($this->mapeoCoberturas->has(1)) {
            foreach ($adicionales as $apf) {
                $mapAdicional = $this->mapeoCoberturas->get(1)->where('sIdTabla', trim($apf->IdAdicionalCf))->first();
                if ($mapAdicional) $coberturas[] = $mapAdicional->sCobertura;
            }
        }

        // 2. Mapeo dinámico por Planes
        foreach ([2, 4] as $origen) {
            if ($this->mapeoCoberturas->has($origen)) {
                foreach ($planes as $p) {
                    $mapPlan = $this->mapeoCoberturas->get($origen)->where('sIdTabla', trim($p->Plan))->first();
                    if ($mapPlan) $coberturas[] = $mapPlan->sCobertura;
                }
            }
        }

        // 3. Lógica para Rubro Salud (2)
        $salud = $planes->where('IdRubroCf', 2)->first();
        if ($salud) {
            $code = trim($salud->Plan);
            $coberturas[] = (strpos($code, 'NIV') === 0) ? 'INI' : substr($code, 0, 3);
        }

        // 4. Lógica para Odontología (3)
        $odo = $planes->where('IdRubroCf', 3)->first();
        if ($odo) {
            $coberturas[] = 'ODO';
        }

        // 5. Lógica para Pieve (6, 7)
        $pieve = $planes->whereIn('IdRubroCf', [6, 7])->first();
        if ($pieve) {
            $coberturas[] = 'PIE';
        }

        // 6. Coberturas UNIVERSALES
        $coberturas[] = 'ODO_BASE';
        $coberturas[] = 'ODO_02';
        $coberturas[] = 'SINSAL';

        // 7. REGLAS REGIONALES
        // Rosario de la Frontera = 42
        if ($localidadId == 42) {
            if (in_array('PIE', $coberturas) || in_array('INI', $coberturas)) {
                return ['PIEVERF', 'ODORF'];
            }
        }
        
        // Metán = 44
        if ($localidadId == 44) {
            if (in_array('PIE', $coberturas) || in_array('INI', $coberturas)) {
                return ['PIEVEMETAN', 'ODONMETAN', 'SINSALMETAN'];
            }
        }

        // 8. NORMALIZAR CÓDIGOS MIGRADOS A ACTUALES
        $coberturasNormalizadas = [];
        foreach ($coberturas as $cov) {
            if (in_array($cov, ['NORTE', 'N70', 'NOR'])) {
                $coberturasNormalizadas[] = 'NORSAL';
            } else {
                $coberturasNormalizadas[] = $cov;
            }
        }

        return array_values(array_unique($coberturasNormalizadas));
    }
}
