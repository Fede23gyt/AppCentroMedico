<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Prueba de Conexión a MS SQL Server ===\n\n";

try {
    // Probar conexión
    $connection = DB::connection('sqlsrv_externa');
    $pdo = $connection->getPdo();

    echo "✓ Conexión establecida exitosamente\n";
    echo "Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
    echo "Versión del servidor: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n\n";

    // Probar consulta simple
    echo "=== Probando consulta a tabla Beneficiarios ===\n";
    $result = $connection->select("SELECT TOP 5 IdTitularCF, Apellido, Nombre, Dni, IdVinculoCF, Existe FROM Beneficiarios WHERE Existe = 1");

    echo "Registros encontrados: " . count($result) . "\n\n";

    if (count($result) > 0) {
        echo "Primeros registros:\n";
        foreach ($result as $row) {
            echo "- Certificado: {$row->IdTitularCF} | ";
            echo "Nombre: {$row->Apellido}, {$row->Nombre} | ";
            echo "DNI: {$row->Dni} | ";
            echo "Vínculo: {$row->IdVinculoCF}\n";
        }
    }

    echo "\n=== Probando modelo BeneficiarioExterno ===\n";
    $beneficiarios = App\Models\BeneficiarioExterno::activos()->take(3)->get();
    echo "Beneficiarios activos encontrados: " . $beneficiarios->count() . "\n";

    foreach ($beneficiarios as $ben) {
        echo "- {$ben->nombre_completo} (DNI: {$ben->Dni})\n";
    }

    echo "\n✓ Todas las pruebas exitosas!\n";

} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
}
