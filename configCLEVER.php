<?php

// =========================================================
// CONNEXION BASE DE DONNEES - CONFIGURATION SECURISEE
// =========================================================

// Chemin du fichier .env
$envFile = __DIR__ . '/.env';

if (!file_exists($envFile)) {
    die("Erreur : fichier .env introuvable.");
}

// Lecture du fichier .env
$lines = file(
    $envFile,
    FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
);

foreach ($lines as $line) {

    $line = trim($line);

    // Ignorer les commentaires
    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }

    // Séparer NOM=VALEUR
    $parts = explode('=', $line, 2);

    if (count($parts) !== 2) {
        continue;
    }

    $name  = trim($parts[0]);
    $value = trim($parts[1]);

    putenv("$name=$value");
}


// =========================================================
// RECUPERATION DES INFORMATIONS MYSQL
// =========================================================

$host = getenv('MYSQL_ADDON_HOST');
$dbname = getenv('MYSQL_ADDON_DB');
$user = getenv('MYSQL_ADDON_USER');
$pass = getenv('MYSQL_ADDON_PASSWORD');
$port = (int) getenv('MYSQL_ADDON_PORT');


// =========================================================
// CONNEXION MYSQL
// =========================================================

$conn = new mysqli(
    $host,
    $user,
    $pass,
    $dbname,
    $port
);


// =========================================================
// VERIFICATION
// =========================================================

if ($conn->connect_error) {
    die("Erreur de connexion à la base de données.");
}


// =========================================================
// ENCODAGE
// =========================================================

$conn->set_charset("utf8mb4");