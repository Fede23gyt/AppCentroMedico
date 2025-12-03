<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Buscar Certificado 110060 ===\n\n";

$certificado = "110060";

try {
    // Prueba 1: Query directa
    echo "Prueba 1: Query SQL directa\n";
    $connection = DB::connection('sqlsrv_externa');

    $sql = "SELECT IdTitularCF, Apellido, Nombre, Dni, IdVinculoCF, Existe
            FROM Beneficiarios
            WHERE IdTitularCF = ?";

    $result = $connection->select($sql, [$certificado]);
    echo "Registros encontrados (sin filtro de Existe): " . count($result) . "\n";

    if (count($result) > 0) {
        foreach ($result as $row) {
            echo "- Certificado: {$row->IdTitularCF} | ";
            echo "Nombre: {$row->Apellido}, {$row->Nombre} | ";
            echo "DNI: {$row->Dni} | ";
            echo "Vínculo: {$row->IdVinculoCF} | ";
            echo "Existe: {$row->Existe}\n";
        }
    } else {
        echo "No se encontraron registros con ese certificado\n";
    }

    echo "\n";

    // Prueba 2: Con filtro Existe = 1
    echo "Prueba 2: Con filtro Existe = 1\n";
    $sql2 = "SELECT IdTitularCF, Apellido, Nombre, Dni, IdVinculoCF, Existe
             FROM Beneficiarios
             WHERE IdTitularCF = ? AND Existe = 1";

    $result2 = $connection->select($sql2, [$certificado]);
    echo "Registros encontrados (con Existe = 1): " . count($result2) . "\n";

    if (count($result2) > 0) {
        foreach ($result2 as $row) {
            echo "- Certificado: {$row->IdTitularCF} | ";
            echo "Nombre: {$row->Apellido}, {$row->Nombre} | ";
            echo "DNI: {$row->Dni} | ";
            echo "Vínculo: {$row->IdVinculoCF}\n";
        }
    } else {
        echo "No se encontraron registros activos\n";
    }

    echo "\n";

    // Prueba 3: Con el modelo
    echo "Prueba 3: Usando modelo BeneficiarioExterno\n";
    $beneficiarios = App\Models\BeneficiarioExterno::buscarPorCertificado($certificado);
    echo "Registros encontrados: " . $beneficiarios->count() . "\n";

    if ($beneficiarios->count() > 0) {
        foreach ($beneficiarios as $ben) {
            echo "- {$ben->nombre_completo} (DNI: {$ben->Dni})\n";
        }
    }

    echo "\n";

    // Prueba 4: Ver tipo de dato del certificado
    echo "Prueba 4: Verificar estructura de la tabla\n";
    $estructura = $connection->select("SELECT TOP 1
        IdTitularCF,
        CAST(IdTitularCF AS VARCHAR) as IdTitularCF_texto
        FROM Beneficiarios
        WHERE IdTitularCF LIKE '%110060%'");

    echo "Búsqueda con LIKE: " . count($estructura) . " registros\n";
    if (count($estructura) > 0) {
        foreach ($estructura as $row) {
            echo "- Valor encontrado: {$row->IdTitularCF} (tipo texto: {$row->IdTitularCF_texto})\n";
        }
    }

} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
