<?php

session_start();

header('Content-Type: text/html; charset=utf-8');


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
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {

    die("Erreur de connexion à la base de données.");
}


/* =====================================================
   CONNEXION CLIENT
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $mail = trim($_POST["mail"] ?? "");
    $password = trim($_POST["accesclient"] ?? "");


    /* =================================================
       VALIDATION
    ================================================= */

    if ($mail === "" || $password === "") {

        die("Champs obligatoires manquants");
    }


    /* =================================================
       RECHERCHER CLIENT
    ================================================= */

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


    /* =================================================
       CLIENT INTROUVABLE
    ================================================= */

    if (!$client) {

        die("Utilisateur introuvable");
    }


    /* =================================================
       VERIFICATION MOT DE PASSE
       LOGIQUE CONSERVÉE
    ================================================= */

    if ($password !== $client["accesclient"]) {

        die("Mot de passe incorrect");
    }


    /* =================================================
       CONNEXION OK
    ================================================= */

    $_SESSION["codeclient"] = $client["codeclient"];
    $_SESSION["Nomclient"] = $client["Nomclient"];
    $_SESSION["telephone"] = $client["telephone"];
    $_SESSION["mail"] = $client["mail"];


    /* =================================================
       UPDATE CNX
    ================================================= */

    $upd = $pdo->prepare("
        UPDATE client
        SET cnx = 1
        WHERE codeclient = ?
    ");

    $upd->execute([
        $client["codeclient"]
    ]);


    /* =================================================
       LOCAL STORAGE + REDIRECTION
    ================================================= */

    $nom = json_encode(
        $client["Nomclient"],
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    );

    $telephone = json_encode(
        $client["telephone"],
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    );

    $mailJS = json_encode(
        $client["mail"],
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    );

    $accesclient = json_encode(
        $client["accesclient"],
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    );


    echo "<script>

        localStorage.setItem('Nomclient', {$nom});

        localStorage.setItem('telephone', {$telephone});

        localStorage.setItem('mail', {$mailJS});

        localStorage.setItem('accesclient', {$accesclient});

        window.location.href = 'colisclient.php';

    </script>";

    exit;
}


/* =====================================================
   AUCUNE REQUETE
===================================================== */

die("Requête invalide");

?>
