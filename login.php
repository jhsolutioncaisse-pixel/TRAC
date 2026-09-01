```php
<?php

declare(strict_types=1);

session_start();

header('Content-Type: text/html; charset=utf-8');


/* =====================================================
   CONFIGURATION MYSQL - NOUVELLE BASE
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
            PDO::ATTR_TIMEOUT             => 10
        ]
    );

} catch (PDOException $e) {

    http_response_code(500);

    die("
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1'>
            <title>Erreur</title>

            <style>
                body {
                    font-family: Arial, sans-serif;
                    background: #f5f7fa;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    margin: 0;
                }

                .box {
                    background: white;
                    padding: 30px;
                    border-radius: 15px;
                    max-width: 500px;
                    width: 90%;
                    text-align: center;
                    box-shadow: 0 10px 30px rgba(0,0,0,.10);
                }

                h2 {
                    color: #dc3545;
                }

                p {
                    color: #666;
                }
            </style>
        </head>

        <body>

            <div class='box'>

                <h2>Erreur de connexion</h2>

                <p>
                    Impossible de se connecter à la base de données.
                </p>

                <p>
                    Veuillez réessayer plus tard.
                </p>

            </div>

        </body>
        </html>
    ");

}


/* =====================================================
   VERIFICATION DE LA METHODE
===================================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    die("Requête invalide.");

}


/* =====================================================
   RECUPERATION DU FORMULAIRE
===================================================== */

$mail = trim($_POST["mail"] ?? "");

$password = trim($_POST["accesclient"] ?? "");


/* =====================================================
   VERIFICATION DES CHAMPS
===================================================== */

if ($mail === "" || $password === "") {

    echo "
        <script>
            alert('Veuillez remplir tous les champs.');
            history.back();
        </script>
    ";

    exit;
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

    echo "
        <script>
            alert('Utilisateur introuvable.');
            history.back();
        </script>
    ";

    exit;
}


/* =====================================================
   VERIFICATION DU MOT DE PASSE
===================================================== */

if ($password !== $client["accesclient"]) {

    echo "
        <script>
            alert('Mot de passe incorrect.');
            history.back();
        </script>
    ";

    exit;
}


/* =====================================================
   CONNEXION REUSSIE
===================================================== */

/*
   On régénère l'identifiant de session
   pour sécuriser la connexion.
*/

session_regenerate_id(true);


/* =====================================================
   SESSION CLIENT
===================================================== */

/*
   IMPORTANT :
   Le téléphone est conservé dans la session.
*/

$_SESSION["codeclient"] = $client["codeclient"];

$_SESSION["Nomclient"] = $client["Nomclient"];

$_SESSION["telephone"] = $client["telephone"];

$_SESSION["mail"] = $client["mail"];


/* =====================================================
   MISE A JOUR DE CNX
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
       la session client reste valide.
    */

}


/* =====================================================
   PREPARATION DES DONNEES POUR LOCAL STORAGE
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
   LOCAL STORAGE
===================================================== */

echo "

<script>

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


/* =====================================================
   REDIRECTION
===================================================== */

window.location.href = 'colisclient.php';

</script>

";


exit;

?>
```
