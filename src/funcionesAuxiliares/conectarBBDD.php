<?php

/**
 * @SuppressWarnings(PHPMD.MissingImport)
 */

function conectarBBDD(): ?\PDO
{
    $host     = 'cah8ha8ra8h8i7.cluster-czz5s0kz4scl.eu-west-1.rds.amazonaws.com';
    $port     = '5432';
    $dbname   = 'dd2e5fnppcb8fb';
    $user     = 'u1d480il6e6sme';
    $password = 'p3a5c8480a4dc4d7fa9d971c5e08dd22dbc6c21b20726f09b863b4879a9309eae';

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

    try {
        $pdo = new \PDO($dsn, $user, $password);
        // Set error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Conexión exitosa
        #echo "Connected to the database successfully!";
        return $pdo;
    } catch (\PDOException $e) {
        // Error de conexión
        error_log('Connection failed: ' . $e->getMessage());
        return null;
    }
}
