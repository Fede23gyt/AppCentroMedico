<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Ver todas las columnas de la tabla Beneficiarios ===\n\n";

try {
    $connection = DB::connection('sqlsrv_externa');

    // Obtener información de las columnas
    $columns = $connection->select("
        SELECT
            COLUMN_NAME,
            DATA_TYPE,
            CHARACTER_MAXIMUM_LENGTH,
            IS_NULLABLE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = 'Beneficiarios'
        ORDER BY ORDINAL_POSITION
    ");

    echo "Columnas de la tabla Beneficiarios:\n\n";

    foreach ($columns as $col) {
        $length = $col->CHARACTER_MAXIMUM_LENGTH ? "({$col->CHARACTER_MAXIMUM_LENGTH})" : '';
        $nullable = $col->IS_NULLABLE == 'YES' ? 'NULL' : 'NOT NULL';
        echo "- {$col->COLUMN_NAME}: {$col->DATA_TYPE}{$length} {$nullable}\n";
    }

    echo "\n=== Ver datos de ejemplo ===\n\n";

    // Ver los primeros registros con todas sus columnas
    $data = $connection->select("SELECT TOP 3 * FROM Beneficiarios WHERE Existe = 1");

    if (count($data) > 0) {
        $firstRow = $data[0];
        echo "Columnas disponibles en el primer registro:\n";
        foreach ($firstRow as $key => $value) {
            echo "- $key = " . var_export($value, true) . "\n";
        }
    }

} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
