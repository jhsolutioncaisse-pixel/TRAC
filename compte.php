<?php

header('Content-Type: application/json');

try {

     $pdo = new PDO(
        "mysql:host=bi4znbakulhrwepehasb-mysql.services.clever-cloud.com;dbname=bi4znbakulhrwepehasb;port=3306;charset=utf8",
        "urwpvypsyyfz8vr9",
        "kqGARbb1nVjSCCe28Blc"
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    echo json_encode([
        "error" => "Connexion base impossible"
    ]);

    exit;
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