
<?php

declare(strict_types=1);

session_start();

echo "ETAPE 1 : PHP fonctionne<br>";
flush();

$host   = "b9xd1ca5virznhlmzgmt-mysql.services.clever-cloud.com";
$dbname = "bi4znbakulhrwepehasb";
$user   = "urwpvypsyyfz8vr9";
$pass   = "kqGARbb1nVjSCCe28Blc";
$port   = 3306;

echo "ETAPE 2 : tentative MySQL...<br>";
flush();

try {

    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5
        ]
    );

    echo "ETAPE 3 : MYSQL CONNECTE !<br>";
    flush();

    $stmt = $pdo->query("SELECT 1");

    echo "ETAPE 4 : REQUETE MYSQL OK !<br>";
    flush();

} catch (Throwable $e) {

    echo "ERREUR : " . htmlspecialchars($e->getMessage());
    exit;
}

echo "ETAPE 5 : FIN";


