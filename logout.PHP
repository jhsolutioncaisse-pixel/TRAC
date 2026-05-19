<?php
session_start();

/* récupérer client */
$codeclient = $_SESSION["codeclient"] ?? null;

if ($codeclient) {

    $pdo = new PDO(
        "mysql:host=bi4znbakulhrwepehasb-mysql.services.clever-cloud.com;
        dbname=bi4znbakulhrwepehasb;
        port=3306;
        charset=utf8",
        "urwpvypsyyfz8vr9",
        "kqGARbb1nVjSCCe28Blc",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

    $stmt = $pdo->prepare("UPDATE client SET cnx = 0 WHERE codeclient = ?");
    $stmt->execute([$codeclient]);
}

/* destruction session */
session_unset();
session_destroy();

/* redirection */
header("Location: ACCEUIL.HTML");
exit;
?>