<?php

declare(strict_types=1);

session_start();

/*
=========================================================
 JH SOLUTION
 LOGIN CLIENT
=========================================================

 Fonctionnement :

 1. Connexion à MySQL
 2. Réception du mail et mot de passe
 3. Recherche du client
 4. Vérification du mot de passe
 5. Création de la session client
 6. Mise à jour de cnx = 1
 7. Redirection vers colisclient.php

 IMPORTANT :
 - Le mot de passe n'est PAS enregistré dans localStorage.
 - Les informations importantes sont conservées dans la session PHP.
=========================================================
*/


/* =====================================================
   CONFIGURATION MYSQL
===================================================== */

$host   = "b9xd1ca5virznhlmzgmt-mysql.services.clever-cloud.com";
$dbname = "bi4znbakulhrwepehasb";
$user   = "urwpvypsyyfz8vr9";
$pass   = "kqGARbb1nVjSCCe28Blc";
$port   = 3306;


/* =====================================================
   CONNEXION PDO
===================================================== */

try {

    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]
    );

} catch (PDOException $e) {

    http_response_code(500);

    die("Erreur de connexion à la base de données.");
}


/* =====================================================
   VÉRIFICATION DE LA REQUÊTE
===================================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    die("Requête invalide.");
}


/* =====================================================
   RÉCUPÉRATION DES DONNÉES
===================================================== */

$mail = trim($_POST["mail"] ?? "");
$password = trim($_POST["accesclient"] ?? "");


/* =====================================================
   VALIDATION
===================================================== */

if ($mail === "" || $password === "") {

    die("Veuillez remplir tous les champs.");
}


/* =====================================================
   NORMALISATION DU MAIL
===================================================== */

$mail = strtolower($mail);


/* =====================================================
   RECHERCHE DU CLIENT
===================================================== */

try {

    $stmt = $pdo->prepare("
        SELECT
            codeclient,
            Nomclient,
            telephone,
            mail,
            accesclient,
            cnx
        FROM client
        WHERE LOWER(mail) = ?
        LIMIT 1
    ");

    $stmt->execute([
        $mail
    ]);

    $client = $stmt->fetch();

} catch (PDOException $e) {

    http_response_code(500);

    die("Erreur lors de la recherche du client.");
}


/* =====================================================
   CLIENT INTROUVABLE
===================================================== */

if (!$client) {

    die("Adresse e-mail ou mot de passe incorrect.");
}


/* =====================================================
   VÉRIFICATION DU MOT DE PASSE
=====================================================

   Cette version reste compatible avec ton système actuel
   dans lequel accesclient contient le mot de passe.

   Si plus tard tu passes les mots de passe en hash,
   cette partie pourra être remplacée par password_verify().
===================================================== */

if (!hash_equals(
    (string)$client["accesclient"],
    (string)$password
)) {

    die("Adresse e-mail ou mot de passe incorrect.");
}


/* =====================================================
   RÉGÉNÉRATION DE L'ID DE SESSION
===================================================== */

session_regenerate_id(true);


/* =====================================================
   CRÉATION DE LA SESSION CLIENT
===================================================== */

$_SESSION["client_connecte"] = true;

$_SESSION["codeclient"] = $client["codeclient"];

$_SESSION["Nomclient"] = $client["Nomclient"];

$_SESSION["telephone"] = $client["telephone"];

$_SESSION["mail"] = $client["mail"];


/*
   NE JAMAIS METTRE LE MOT DE PASSE
   DANS LA SESSION OU DANS localStorage.
*/


/* =====================================================
   MISE À JOUR DE LA CONNEXION
===================================================== */

try {

    $upd = $pdo->prepare("
        UPDATE client
        SET cnx = 1
        WHERE codeclient = ?
    ");

    $upd->execute([
        $client["codeclient"]
    ]);

} catch (PDOException $e) {

    /*
       La connexion est déjà valide.
       On ne bloque pas l'utilisateur si uniquement
       la mise à jour de cnx rencontre un problème.
    */
}


/* =====================================================
   LOCAL STORAGE
=====================================================

   On conserve uniquement les informations non sensibles
   éventuellement utilisées par ton interface JavaScript.

   IMPORTANT :
   accesclient est volontairement supprimé.
===================================================== */

$nom = json_encode(
    (string)$client["Nomclient"],
    JSON_UNESCAPED_UNICODE |
    JSON_HEX_TAG |
    JSON_HEX_APOS |
    JSON_HEX_QUOT |
    JSON_HEX_AMP
);

$telephone = json_encode(
    (string)$client["telephone"],
    JSON_UNESCAPED_UNICODE |
    JSON_HEX_TAG |
    JSON_HEX_APOS |
    JSON_HEX_QUOT |
    JSON_HEX_AMP
);

$mailJS = json_encode(
    (string)$client["mail"],
    JSON_UNESCAPED_UNICODE |
    JSON_HEX_TAG |
    JSON_HEX_APOS |
    JSON_HEX_QUOT |
    JSON_HEX_AMP
);

$codeclient = json_encode(
    (string)$client["codeclient"],
    JSON_UNESCAPED_UNICODE |
    JSON_HEX_TAG |
    JSON_HEX_APOS |
    JSON_HEX_QUOT |
    JSON_HEX_AMP
);


/* =====================================================
   REDIRECTION
===================================================== */

echo "<!DOCTYPE html>
<html lang='fr'>
<head>

<meta charset='UTF-8'>

<meta name='viewport' content='width=device-width, initial-scale=1.0'>

<title>Connexion...</title>

</head>

<body>

<script>

try {

    localStorage.setItem('codeclient', {$codeclient});

    localStorage.setItem('Nomclient', {$nom});

    localStorage.setItem('telephone', {$telephone});

    localStorage.setItem('mail', {$mailJS});

} catch (e) {

    console.error('Erreur localStorage :', e);

}

window.location.replace('colisclient.php');

</script>

</body>

</html>";

exit;

?>
