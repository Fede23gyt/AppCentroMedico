<?php

echo "=== Prueba de Conexión con extensión SQLSRV nativa ===\n\n";

$serverName = "10.0.0.252,1433";
$database = "pievesalud";
$username = "sa";
$password = "xml746";

echo "Servidor: $serverName\n";
echo "Base de datos: $database\n";
echo "Usuario: $username\n\n";

// Probar con la extensión sqlsrv nativa (no PDO)
$connectionInfo = array(
    "Database" => $database,
    "UID" => $username,
    "PWD" => $password,
    "CharacterSet" => "UTF-8",
    "ReturnDatesAsStrings" => true
);

echo "Intento 1: Conexión básica...\n";
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    echo "✗ Error en conexión básica\n";
    print_r(sqlsrv_errors());

    // Intentar sin puerto
    echo "\nIntento 2: Sin especificar puerto...\n";
    $serverName2 = "10.0.0.252";
    $conn = sqlsrv_connect($serverName2, $connectionInfo);

    if ($conn === false) {
        echo "✗ Error en conexión sin puerto\n";
        print_r(sqlsrv_errors());

        // Intentar con TrustServerCertificate
        echo "\nIntento 3: Con TrustServerCertificate...\n";
        $connectionInfo['TrustServerCertificate'] = true;
        $conn = sqlsrv_connect($serverName, $connectionInfo);

        if ($conn === false) {
            echo "✗ Error con TrustServerCertificate\n";
            print_r(sqlsrv_errors());

            // Intentar sin Encrypt
            echo "\nIntento 4: Deshabilitando encriptación...\n";
            $connectionInfo['Encrypt'] = false;
            $conn = sqlsrv_connect($serverName, $connectionInfo);

            if ($conn === false) {
                echo "✗ Error deshabilitando encriptación\n";
                print_r(sqlsrv_errors());
                die();
            }
        }
    }
}

echo "✓ ¡Conexión exitosa!\n\n";

// Probar una consulta
echo "=== Probando consulta a Beneficiarios ===\n";
$sql = "SELECT TOP 5 IdTitularCF, Apellido, Nombre, Dni, IdVinculoCF FROM Beneficiarios WHERE Existe = 1";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo "✗ Error en consulta:\n";
    print_r(sqlsrv_errors());
} else {
    echo "✓ Consulta exitosa\n";
    echo "Registros encontrados:\n\n";

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        echo "- Certificado: {$row['IdTitularCF']} | ";
        echo "Nombre: {$row['Apellido']}, {$row['Nombre']} | ";
        echo "DNI: {$row['Dni']} | ";
        echo "Vínculo: {$row['IdVinculoCF']}\n";
    }

    sqlsrv_free_stmt($stmt);
}

sqlsrv_close($conn);

echo "\n✓ Todas las pruebas completadas exitosamente!\n";
