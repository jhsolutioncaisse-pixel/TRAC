<?php
header("Content-Type: application/json");

/* ===============================
   CONNEXION MYSQL CLEVER CLOUD
   =============================== */

$host    = "bmxwrvykt5gv0y88jvj3-mysql.services.clever-cloud.com";
$dbname  = "bi4znbakulhrwepehasb";
$user    = "urwpvypsyyfz8vr9";
$pass    = "kqGARbb1nVjSCCe28Blc";
$port    = 3306;
$charset = "utf8mb4";

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";

try {

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

} catch(PDOException $e){

    echo json_encode([
        "message" => "Connexion échouée"
    ]);
    exit;
}

/* ===============================
   RECHERCHE CLIENT
   =============================== */

if(isset($_GET['telephone'])){

    $telephone = $_GET['telephone'];

    $sql = "SELECT * FROM client WHERE telephone=?";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([$telephone]);

    $client = $stmt->fetch();

    if($client){

        echo json_encode([
            "found" => true,
            "data" => $client
        ]);

    } else {

        echo json_encode([
            "found" => false
        ]);
    }

    exit;
}

/* ===============================
   VARIABLES
   =============================== */

$codeclient  = $_POST['codeclient'] ?? '';
$Nomclient   = $_POST['Nomclient'] ?? '';
$telephone   = $_POST['telephone'] ?? '';
$mail        = $_POST['mail'] ?? '';
$accesclient = $_POST['accesclient'] ?? '';

/* ===============================
   VALIDATION
   =============================== */

if(
    empty($Nomclient) ||
    empty($telephone) ||
    empty($mail) ||
    empty($accesclient)
){

    echo json_encode([
        "message" => "Tous les champs sont obligatoires"
    ]);

    exit;
}

/* ===============================
   MODIFICATION
   =============================== */

if($codeclient != ''){

    $sql = "UPDATE client
            SET Nomclient=?,
                telephone=?,
                mail=?,
                accesclient=?
            WHERE codeclient=?";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $Nomclient,
        $telephone,
        $mail,
        $accesclient,
        $codeclient
    ]);

    echo json_encode([
        "message" => "Compte modifié avec succès"
    ]);

} else {

    /* ===============================
       CREATION
       =============================== */

    $sql = "INSERT INTO client(
                Nomclient,
                telephone,
                mail,
                accesclient
            )
            VALUES(?,?,?,?)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $Nomclient,
        $telephone,
        $mail,
        $accesclient
    ]);

    echo json_encode([
        "message" => "Compte créé avec succès"
    ]);
}
?>