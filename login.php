<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

session_start();

echo "<h3>Test de connexion MySQL</h3>";

$host   = "bi4znbakulhrwepehasb-mysql.services.clever-cloud.com";
$dbname = "bi4znbakulhrwepehasb";
$user   = "urwpvypsyyfz8vr9";
$pass   = "kqGARbb1nVjSCCe28Blc";
$port   = 3306;

try {

    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 10
        ]
    );

    echo "<p style='color:green;font-weight:bold;'>
        CONNEXION MYSQL REUSSIE
    </p>";

    $test = $pdo->query("SELECT 1");
    
    echo "<p style='color:green;'>
        TEST SQL REUSSI
    </p>";

    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM client");

    $result = $stmt->fetch();

    echo "<p style='color:green;'>
        TABLE client OK
    </p>";

    echo "<p>
        Nombre de clients : " .
        htmlspecialchars((string)$result['total']) .
    "</p>";

} catch (PDOException $e) {

    echo "<div style='
        background:#ffe5e5;
        border:2px solid #dc3545;
        padding:20px;
        margin:20px;
        border-radius:10px;
        color:#842029;
    '>";

    echo "<h3>ERREUR MYSQL</h3>";

    echo "<pre>";
    echo htmlspecialchars($e->getMessage());
    echo "</pre>";

    echo "</div>";

    exit;
}

?>
