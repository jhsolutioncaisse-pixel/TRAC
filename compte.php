<?php

header('Content-Type: application/json');


$host   = "b9xd1ca5virznhlmzgmt-mysql.services.clever-cloud.com";
$dbname = "b9xd1ca5virznhlmzgmt";
$user   = "usm9pm3hnlnhmoee";
$pass   = "5un1mBwofPvYnS36hOLi";
$port   = 20856;

$conn = new mysqli(
    $host,
    $user,
    $pass,
    $dbname,
    $port
);

if ($conn->connect_error) {
    die("Erreur de connexion à la base de données.");
}

 

/* =========================================
   RECHERCHE CLIENT
========================================= */

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["telephone"])) {

    $telephone = trim($_GET["telephone"]);

    $sql = "SELECT *
            FROM client
            WHERE telephone = ?
            LIMIT 1";

    $req = $pdo->prepare($sql);

    $req->execute([$telephone]);

    $client = $req->fetch(PDO::FETCH_ASSOC);

    if ($client) {

        echo json_encode([
            "found" => true,
            "data" => [
                "codeclient"   => $client["codeclient"],
                "Nomclient"    => $client["Nomclient"],
                "telephone"    => $client["telephone"],
                "mail"         => $client["mail"],
                "accesclient"  => $client["accesclient"]
            ]
        ]);

    } else {

        echo json_encode([
            "found" => false
        ]);
    }

    exit;
}

/* =========================================
   AJOUT / MODIFICATION
========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $codeclient  = trim($_POST["codeclient"] ?? "");
    $Nomclient   = trim($_POST["Nomclient"] ?? "");
    $telephone   = trim($_POST["telephone"] ?? "");
    $mail        = trim($_POST["mail"] ?? "");
    $accesclient = trim($_POST["accesclient"] ?? "");

    /* VALIDATION */

    if (
        $Nomclient === "" ||
        $telephone === "" ||
        $mail === "" ||
        $accesclient === ""
    ) {

        echo json_encode([
            "error" => "Tous les champs sont obligatoires"
        ]);

        exit;
    }

    /* =====================================
       MODIFICATION
    ===================================== */

    if ($codeclient !== "") {

        $sql = "UPDATE client SET

                    Nomclient   = ?,
                    telephone   = ?,
                    mail        = ?,
                    accesclient = ?

                WHERE codeclient = ?";

        $req = $pdo->prepare($sql);

        $req->execute([
            $Nomclient,
            $telephone,
            $mail,
            $accesclient,
            $codeclient
        ]);

        echo json_encode([
            "message" => "Compte modifié avec succès"
        ]);

        exit;
    }

    /* =====================================
       CREATION
    ===================================== */

    $check = $pdo->prepare("
        SELECT codeclient
        FROM client
        WHERE telephone = ?
    ");

    $check->execute([$telephone]);

    if ($check->fetch()) {

        echo json_encode([
            "error" => "Ce numéro existe déjà"
        ]);

        exit;
    }

    $insert = $pdo->prepare("

        INSERT INTO client (

            Nomclient,
            telephone,
            mail,
            accesclient

        ) VALUES (?,?,?,?)

    ");

    $insert->execute([
        $Nomclient,
        $telephone,
        $mail,
        $accesclient
    ]);

    echo json_encode([
        "message" => "Compte créé avec succès"
    ]);

    exit;
}

/* =========================================
   SI AUCUNE REQUETE VALIDE
========================================= */

echo json_encode([
    "error" => "Requête invalide"
]);
?>
