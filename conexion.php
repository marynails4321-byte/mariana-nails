<?php
// Intentar obtener la URL de conexión que proporciona Render automáticamente
$db_url = getenv('DATABASE_URL');

try {
    if ($db_url) {
        // --- CONFIGURACIÓN AUTOMÁTICA EN RENDER (PostgreSQL) ---
        $dbopts = parse_url($db_url);
        
        $host = $dbopts["host"];
        $port = isset($dbopts["port"]) ? $dbopts["port"] : "5432";
        $user = $dbopts["user"];
        $pass = $dbopts["pass"];
        $db   = ltrim($dbopts["path"], '/');

        // Conexión segura con SSL para PostgreSQL en Render
        $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db;sslmode=require", $user, $pass);
        
    } else {
        // --- CONFIGURACIÓN LOCAL (Para cuando pruebes en tu computadora con XAMPP/MySQL) ---
        $host = '127.0.0.1'; 
        $db   = 'mariananails'; // Cambia esto al nombre de tu base de datos local si usas una
        $user = 'root';
        $pass = '';

        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    }

    // Configurar para que lance excepciones si ocurre algún error en las consultas
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Si falla la conexión, muestra un mensaje claro
    die("Error en la conexión a la base de datos: " . $e->getMessage());
}
?>