<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Verificar campo de vínculo en MSSQL ===\n\n";

$certificado = "110060";

try {
    $connection = DB::connection('sqlsrv_externa');

    // Ver la estructura completa de los registros
    $sql = "SELECT TOP 10
        IdBenCP,
        IdTitularCF,
        Apellido,
        Nombre,
        Dni,
        IdVinculoCf,
        Existe
        FROM Beneficiarios
        WHERE IdTitularCF = ?
        ORDER BY IdVinculoCf";

    $result = $connection->select($sql, [$certificado]);

    echo "Registros encontrados: " . count($result) . "\n\n";

    foreach ($result as $row) {
        echo "IdBenCP: {$row->IdBenCP}\n";
        echo "Certificado: {$row->IdTitularCF}\n";
        echo "Nombre: {$row->Apellido}, {$row->Nombre}\n";
        echo "DNI: {$row->Dni}\n";
        echo "IdVinculoCf: " . var_export($row->IdVinculoCf ?? 'NULL', true) . " (tipo: " . gettype($row->IdVinculoCf ?? null) . ")\n";
        echo "Existe: {$row->Existe}\n";
        echo "---\n";
    }

    echo "\n=== Verificar nombres de columnas ===\n";
    $columns = $connection->select("SELECT TOP 1 * FROM Beneficiarios WHERE IdTitularCF = ?", [$certificado]);

    if (count($columns) > 0) {
        $firstRow = $columns[0];
        echo "Columnas disponibles:\n";
        foreach ($firstRow as $key => $value) {
            echo "- $key: " . var_export($value, true) . " (tipo: " . gettype($value) . ")\n";
        }
    }

} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
