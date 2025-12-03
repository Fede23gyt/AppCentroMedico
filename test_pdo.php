<?php

echo "=== Prueba de Conexión PDO Directa ===\n\n";

$host = "10.0.0.252";
$port = "1433";
$database = "pievesalus";
$username = "sa";
$password = "xml746";

echo "Host: $host\n";
echo "Puerto: $port\n";
echo "Base de datos: $database\n";
echo "Usuario: $username\n\n";

try {
    // Intentar con diferentes formatos de DSN

    echo "Intento 1: sqlsrv:Server=host,port...\n";
    $dsn1 = "sqlsrv:Server=$host,$port;Database=$database";
    echo "DSN: $dsn1\n";
    $conn = new PDO($dsn1, $username, $password);
    echo "✓ Conexión exitosa!\n";

    // Probar una consulta simple
    $stmt = $conn->query("SELECT @@VERSION as version");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Versión SQL Server: " . $result['version'] . "\n";

} catch (PDOException $e) {
    echo "✗ Error con DSN 1: " . $e->getMessage() . "\n\n";

    try {
        echo "Intento 2: sqlsrv:Server=host;Database=...\n";
        $dsn2 = "sqlsrv:Server=$host;Database=$database";
        echo "DSN: $dsn2\n";
        $conn = new PDO($dsn2, $username, $password);
        echo "✓ Conexión exitosa!\n";

    } catch (PDOException $e2) {
        echo "✗ Error con DSN 2: " . $e2->getMessage() . "\n\n";

        try {
            echo "Intento 3: sqlsrv con opciones adicionales...\n";
            $dsn3 = "sqlsrv:Server=$host,$port;Database=$database;Encrypt=no;TrustServerCertificate=yes";
            echo "DSN: $dsn3\n";
            $conn = new PDO($dsn3, $username, $password);
            echo "✓ Conexión exitosa!\n";

        } catch (PDOException $e3) {
            echo "✗ Error con DSN 3: " . $e3->getMessage() . "\n\n";

            echo "=== Información de diagnóstico ===\n";
            echo "Extensiones PDO cargadas:\n";
            $drivers = PDO::getAvailableDrivers();
            foreach ($drivers as $driver) {
                echo "- $driver\n";
            }
        }
    }
}
