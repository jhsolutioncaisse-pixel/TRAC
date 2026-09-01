<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

/* =====================================================
   CONFIGURATION MYSQL
===================================================== */

$host = "bi4znbakulhrwepehasb-mysql.services.clever-cloud.com";
$dbname = "bi4znbakulhrwepehasb";
$user = "urwpvypsyyfz8vr9";
$pass = "kqGARbb1nVjSCCe28Blc";
$port = 3306;


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

    http_response_code(500);

    echo json_encode([
        "error" => "Erreur de connexion à la base de données"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/* =====================================================
   RECHERCHE CLIENT PAR TELEPHONE
===================================================== */

if (
    $_SERVER["REQUEST_METHOD"] === "GET" &&
    isset($_GET["telephone"])
) {

    $telephone = trim((string) $_GET["telephone"]);

    if ($telephone === "") {

        echo json_encode([
            "found" => false,
            "error" => "Numéro de téléphone obligatoire"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    try {

        $sql = "
            SELECT
                codeclient,
                Nomclient,
                telephone,
                mail,
                accesclient
            FROM client
            WHERE telephone = ?
            LIMIT 1
        ";

        $req = $pdo->prepare($sql);

        $req->execute([
            $telephone
        ]);

        $client = $req->fetch();

        if ($client) {

            echo json_encode([
                "found" => true,
                "data" => [
                    "codeclient"  => $client["codeclient"],
                    "Nomclient"   => $client["Nomclient"],
                    "telephone"   => $client["telephone"],
                    "mail"        => $client["mail"],
                    "accesclient" => $client["accesclient"]
                ]
            ], JSON_UNESCAPED_UNICODE);

        } else {

            echo json_encode([
                "found" => false
            ], JSON_UNESCAPED_UNICODE);
        }

    } catch (PDOException $e) {

        http_response_code(500);

        echo json_encode([
            "error" => "Erreur lors de la recherche du client"
        ], JSON_UNESCAPED_UNICODE);
    }

    exit;
}


/* =====================================================
   AJOUT / MODIFICATION CLIENT
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $codeclient = trim((string) ($_POST["codeclient"] ?? ""));
    $Nomclient = trim((string) ($_POST["Nomclient"] ?? ""));
    $telephone = trim((string) ($_POST["telephone"] ?? ""));
    $mail = trim((string) ($_POST["mail"] ?? ""));
    $accesclient = trim((string) ($_POST["accesclient"] ?? ""));

    /* =================================================
       VALIDATION
    ================================================= */

    if (
        $Nomclient === "" ||
        $telephone === "" ||
        $mail === "" ||
        $accesclient === ""
    ) {

        echo json_encode([
            "error" => "Tous les champs sont obligatoires"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    try {

        /* =============================================
           MODIFICATION
        ============================================= */

        if ($codeclient !== "") {

            $check = $pdo->prepare("
                SELECT codeclient
                FROM client
                WHERE codeclient = ?
                LIMIT 1
            ");

            $check->execute([
                $codeclient
            ]);

            if (!$check->fetch()) {

                echo json_encode([
                    "error" => "Client introuvable"
                ], JSON_UNESCAPED_UNICODE);

                exit;
            }


            /* Vérifier téléphone doublon */

            $checkTelephone = $pdo->prepare("
                SELECT codeclient
                FROM client
                WHERE telephone = ?
                AND codeclient <> ?
                LIMIT 1
            ");

            $checkTelephone->execute([
                $telephone,
                $codeclient
            ]);

            if ($checkTelephone->fetch()) {

                echo json_encode([
                    "error" => "Ce numéro existe déjà pour un autre client"
                ], JSON_UNESCAPED_UNICODE);

                exit;
            }


            /* Mise à jour */

            $sql = "
                UPDATE client SET
                    Nomclient = ?,
                    telephone = ?,
                    mail = ?,
                    accesclient = ?
                WHERE codeclient = ?
            ";

            $req = $pdo->prepare($sql);

            $req->execute([
                $Nomclient,
                $telephone,
                $mail,
                $accesclient,
                $codeclient
            ]);

            echo json_encode([
                "success" => true,
                "message" => "Compte modifié avec succès",
                "codeclient" => $codeclient
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }


        /* =============================================
           CREATION
        ============================================= */

        $check = $pdo->prepare("
            SELECT codeclient
            FROM client
            WHERE telephone = ?
            LIMIT 1
        ");

        $check->execute([
            $telephone
        ]);

        if ($check->fetch()) {

            echo json_encode([
                "error" => "Ce numéro existe déjà"
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }


        /* =============================================
           INSERTION
        ============================================= */

        $insert = $pdo->prepare("
            INSERT INTO client (
                Nomclient,
                telephone,
                mail,
                accesclient
            )
            VALUES (?, ?, ?, ?)
        ");

        $insert->execute([
            $Nomclient,
            $telephone,
            $mail,
            $accesclient
        ]);

        $nouveauCodeClient = $pdo->lastInsertId();

        echo json_encode([
            "success" => true,
            "message" => "Compte créé avec succès",
            "codeclient" => $nouveauCodeClient
        ], JSON_UNESCAPED_UNICODE);

        exit;

    } catch (PDOException $e) {

        http_response_code(500);

        echo json_encode([
            "error" => "Erreur lors de l'enregistrement du client"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}


/* =====================================================
   AUCUNE REQUETE VALIDE
===================================================== */

http_response_code(400);

echo json_encode([
    "error" => "Requête invalide"
], JSON_UNESCAPED_UNICODE);

exit;

?>


    
