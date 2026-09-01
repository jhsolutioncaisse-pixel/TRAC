```php
<?php

declare(strict_types=1);

session_start();

header('Content-Type: text/html; charset=utf-8');


/* =====================================================
   CONFIGURATION MYSQL DIRECTE - CLEVER CLOUD
===================================================== */

$host   = "bi4znbakulhrwepehasb-mysql.services.clever-cloud.com";
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
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 10
        ]
    );

} catch (PDOException $e) {

    http_response_code(500);

    die("Erreur de connexion à la base de données.");
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

    $stmt->execute([$mail]);

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
            alert('Utilisateur introuvable.');
            history.back();
        </script>
    ");
}


/* =====================================================
   VERIFICATION DU MOT DE PASSE
===================================================== */

if ($password !== $client["accesclient"]) {

    die("
        <script>
            alert('Mot de passe incorrect.');
            history.back();
        </script>
    ");
}


/* =====================================================
   CONNEXION REUSSIE
===================================================== */

session_regenerate_id(true);


/* =====================================================
   SESSION CLIENT
===================================================== */

$_SESSION["codeclient"] = $client["codeclient"];
$_SESSION["Nomclient"] = $client["Nomclient"];
$_SESSION["telephone"] = $client["telephone"];
$_SESSION["mail"] = $client["mail"];


/* =====================================================
   METTRE CNX A 1
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
       La connexion reste valide même si
       la mise à jour de cnx échoue.
    */
}


/* =====================================================
   PREPARATION LOCALSTORAGE
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
   LOCALSTORAGE
===================================================== */

echo "<script>

localStorage.setItem('Nomclient', {$nom});

localStorage.setItem('telephone', {$telephone});

localStorage.setItem('mail', {$mailJS});

localStorage.setItem('accesclient', {$accesclient});


/* =====================================================
   REDIRECTION
===================================================== */

window.location.href = 'colisclient.php';

</script>";

exit;

?>
```
