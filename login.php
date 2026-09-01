```php
<?php

declare(strict_types=1);

session_start();

header('Content-Type: text/html; charset=utf-8');


/* =====================================================
   CONFIGURATION MYSQL - CLEVER CLOUD
===================================================== */

/*
   Clever Cloud fournit normalement ces variables
   automatiquement à l'application.
*/

$host = getenv('MYSQL_ADDON_HOST') ?: 'bi4znbakulhrwepehasb-mysql.services.clever-cloud.com';

$dbname = getenv('MYSQL_ADDON_DB') ?: 'bi4znbakulhrwepehasb';

$user = getenv('MYSQL_ADDON_USER') ?: 'urwpvypsyyfz8vr9';

$pass = getenv('MYSQL_ADDON_PASSWORD') ?: 'kqGARbb1nVjSCCe28Blc';

$port = getenv('MYSQL_ADDON_PORT') ?: '3306';


/* =====================================================
   CONNEXION PDO
===================================================== */

try {

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT             => 10
        ]
    );

} catch (PDOException $e) {

    http_response_code(500);

    die("
        <div style='
            font-family:Arial;
            max-width:600px;
            margin:50px auto;
            padding:25px;
            border-radius:10px;
            background:#f8d7da;
            color:#842029;
            border:1px solid #f5c2c7;
        '>
            <h3>Erreur de connexion</h3>
            <p>Impossible de se connecter à la base de données.</p>
            <p>Veuillez réessayer plus tard.</p>
        </div>
    ");

}


/* =====================================================
   VERIFICATION DE LA REQUETE
===================================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    die("Requête invalide.");
}


/* =====================================================
   RECUPERATION DES DONNEES
===================================================== */

$mail = trim($_POST["mail"] ?? "");

$password = trim($_POST["accesclient"] ?? "");


/* =====================================================
   VALIDATION
===================================================== */

if ($mail === "" || $password === "") {

    die("
        <script>
            alert('Veuillez remplir tous les champs.');
            history.back();
        </script>
    ");
}


/* =====================================================
   NETTOYAGE EMAIL
===================================================== */

$mail = filter_var($mail, FILTER_SANITIZE_EMAIL);


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
        WHERE mail = ?
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

    die("
        <script>
            alert('Adresse e-mail ou mot de passe incorrect.');
            history.back();
        </script>
    ");
}


/* =====================================================
   VERIFICATION MOT DE PASSE
===================================================== */

/*
   Cette partie conserve ta logique actuelle :
   le mot de passe saisi est comparé directement
   à la valeur enregistrée dans accesclient.
*/

if ($password !== $client["accesclient"]) {

    die("
        <script>
            alert('Adresse e-mail ou mot de passe incorrect.');
            history.back();
        </script>
    ");
}


/* =====================================================
   CONNEXION REUSSIE
===================================================== */

session_regenerate_id(true);


/* =====================================================
   CREATION DE LA SESSION
===================================================== */

$_SESSION["codeclient"] = $client["codeclient"];

$_SESSION["Nomclient"] = $client["Nomclient"];

$_SESSION["telephone"] = $client["telephone"];

$_SESSION["mail"] = $client["mail"];


/* =====================================================
   MISE A JOUR DE LA CONNEXION
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
       Même si la mise à jour de cnx échoue,
       la connexion du client reste possible.
    */

}


/* =====================================================
   PREPARATION DES DONNEES JAVASCRIPT
===================================================== */

$nom = json_encode(
    $client["Nomclient"],
    JSON_UNESCAPED_UNICODE |
    JSON_HEX_TAG |
    JSON_HEX_APOS |
    JSON_HEX_QUOT |
    JSON_HEX_AMP
);

$telephone = json_encode(
    $client["telephone"],
    JSON_UNESCAPED_UNICODE |
    JSON_HEX_TAG |
    JSON_HEX_APOS |
    JSON_HEX_QUOT |
    JSON_HEX_AMP
);

$mailJS = json_encode(
    $client["mail"],
    JSON_UNESCAPED_UNICODE |
    JSON_HEX_TAG |
    JSON_HEX_APOS |
    JSON_HEX_QUOT |
    JSON_HEX_AMP
);

$accesclient = json_encode(
    $client["accesclient"],
    JSON_UNESCAPED_UNICODE |
    JSON_HEX_TAG |
    JSON_HEX_APOS |
    JSON_HEX_QUOT |
    JSON_HEX_AMP
);


/* =====================================================
   STOCKAGE LOCAL + REDIRECTION
===================================================== */

echo "<script>

    localStorage.setItem(
        'Nomclient',
        {$nom}
    );

    localStorage.setItem(
        'telephone',
        {$telephone}
    );

    localStorage.setItem(
        'mail',
        {$mailJS}
    );

    localStorage.setItem(
        'accesclient',
        {$accesclient}
    );

    window.location.replace('colisclient.php');

</script>";

exit;

?>
```
