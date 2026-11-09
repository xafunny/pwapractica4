<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'tienda_tech');
define('DB_USER', 'root');
define('DB_PASS', '');
define('SITE_NAME', 'Tienda Tech');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function conectar(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            die('Error de conexión a la base de datos. Revisa config.php y que hayas importado database.sql.');
        }
    }
    return $pdo;
}
