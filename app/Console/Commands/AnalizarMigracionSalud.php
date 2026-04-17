<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AnalizarMigracionSalud extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:analizar-migracion-salud {--limit=100 : Cantidad de registros a procesar} {--certificate= : Filtrar por número de certificado/titular}';

    protected $description = 'Analiza la migración de Salud y calcula la equivalencia de planes sin afectar producción.';

    protected $mapeoCoberturas = [];

    public function handle()
    {
        $limit = $this->option('limit');
        $certificate = $this->option('certificate');
        
        $this->info("Iniciando análisis de migración...");

        // 0. Pre-cargar el mapeo de tb_Tabla_Cobertura (solo 72 registros)
        $this->mapeoCoberturas = \DB::connection('sqlsrv_externa')
            ->table('tb_Tabla_Cobertura')
            ->select('iIdOrigenTabla', 'sIdTabla', 'sCobertura', 'iIdRubro')
            ->get()
            ->groupBy('iIdOrigenTabla');

        // Query básico
        $query = \DB::connection('sqlsrv_externa')
            ->table('Beneficiarios')
            ->join('Fichas', 'Beneficiarios.IdTitularCF', '=', 'Fichas.IdTitularCp')
            ->where('Beneficiarios.Existe', 1)
            ->where('Fichas.Existe', 1)
            ->select('Beneficiarios.*', 'Fichas.IdLocalidadAltaCf');

        if ($this->option('certificate')) {
            $certificate = $this->option('certificate');
            $this->info("Filtrando por Certificado: $certificate");
            $query->where('Beneficiarios.IdTitularCF', $certificate);
        } elseif ($this->option('limit') > 0) {
            $limit = $this->option('limit');
            $this->info("Procesando con Límite: $limit");
            $query->limit($limit);
        } else {
            $this->info("Procesando TODO el espectro de afiliados...");
        }

        // Truncar tabla de análisis para empezar limpio
        \App\Models\AnalisisMigracionAfiliado::truncate();

        // Usamos count() para la barra de progreso
        $total = $query->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        // Optimización: Procesar manualmente en bloques para evitar OFFSET
        $chunkSize = 1000;
        $processed = 0;
        $lastId = 0;

        while (true) {
            $batchQuery = clone $query;
            $beneficiarios = $batchQuery->where('Beneficiarios.IdBenCP', '>', $lastId)
                ->orderBy('Beneficiarios.IdBenCP', 'asc')
                ->limit($chunkSize)
                ->get();

            if ($beneficiarios->isEmpty() || ($limit > 0 && $processed >= $limit)) {
                break;
            }

            $titularIds = $beneficiarios->pluck('IdTitularCF')->unique()->toArray();
            
            $todosLosPlanes = \DB::connection('sqlsrv_externa')
                ->table('PlanesFichas')
                ->whereIn('IdTitularCF', $titularIds)
                ->get()
                ->groupBy('IdTitularCF');

            $todosLosAdicionales = \DB::connection('sqlsrv_externa')
                ->table('AdicionalPlanFicha')
                ->whereIn('IdTitularCf', $titularIds)
                ->get()
                ->groupBy('IdTitularCf');

            $datosNuevos = [];

            foreach ($beneficiarios as $ben) {
                $planesFicha = $todosLosPlanes->get($ben->IdTitularCF, collect());
                $adicionales = $todosLosAdicionales->get($ben->IdTitularCF, collect());

                $planSalud = $planesFicha->where('IdRubroCf', 2)->first()?->Plan; 
                $planOdo = $planesFicha->where('IdRubroCf', 3)->first()?->Plan;   
                $planPieve = $planesFicha->whereIn('IdRubroCf', [6, 7])->first()?->Plan;

                $coberturasAsignadas = $this->determinarCoberturasProceso($planesFicha, $adicionales, $ben->IdLocalidadAltaCf);

                $datosNuevos[] = [
                    'id_ben_cp' => $ben->IdBenCP,
                    'id_titular_cf' => $ben->IdTitularCF,
                    'dni' => $ben->Dni,
                    'apellido_nombre' => $ben->Apellido . ', ' . $ben->Nombre,
                    'vinculo' => $ben->IdVinculoCf,
                    'plan_salud' => $planSalud,
                    'plan_odo' => $planOdo,
                    'plan_pieve' => $planPieve,
                    'cobertura_inferida' => implode(', ', $coberturasAsignadas),
                    'plan_centro_medico_id' => null,
                    'plan_centro_medico_nombre' => null,
                    'observaciones' => count($coberturasAsignadas) == 0 ? "Sin cobertura detectada" : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $lastId = $ben->IdBenCP;
                $processed++;
                $bar->advance();
                if ($limit > 0 && $processed >= $limit) break;
            }

            \App\Models\AnalisisMigracionAfiliado::insert($datosNuevos);
        }

        $bar->finish();
        $this->info("\n\nAnálisis completo finalizado para $total registros.");
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
            $code = $salud->Plan;
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

        // 7. REGLAS REGIONALES (REEMPLAZO/ADICION según el caso)
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

        return array_unique($coberturas);
    }
}
