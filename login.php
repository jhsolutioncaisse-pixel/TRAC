<?php
session_start();

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

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $mail = $_POST["mail"] ?? "";
    $password = $_POST["accesclient"] ?? "";

    if (empty($mail) || empty($password)) {
        die("Champs obligatoires manquants");
    }

    // 🔎 chercher client
    $stmt = $pdo->prepare("
        SELECT codeclient, Nomclient, telephone, mail, accesclient, cnx
        FROM client
        WHERE mail = ?
        LIMIT 1
    ");

    $stmt->execute([$mail]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Utilisateur introuvable");
    }

    // 🔐 vérification mot de passe
    if ($password !== $user["accesclient"]) {
        die("Mot de passe incorrect");
    }

    // ✅ connexion OK
    $_SESSION["codeclient"] = $user["codeclient"];
    $_SESSION["Nomclient"] = $user["Nomclient"];
    $_SESSION["telephone"] = $user["telephone"];
    $_SESSION["mail"] = $user["mail"];

    // 🔄 update cnx = 1
    $upd = $pdo->prepare("UPDATE client SET cnx = 1 WHERE codeclient = ?");
    $upd->execute([$user["codeclient"]]);

    // 📦 envoi vers JS localStorage via session redirect
    echo "<script>
        localStorage.setItem('Nomclient', '".addslashes($user["Nomclient"])."');
        localStorage.setItem('telephone', '".addslashes($user["telephone"])."');
        localStorage.setItem('mail', '".addslashes($user["mail"])."');
        localStorage.setItem('accesclient', '".addslashes($user["accesclient"])."');

        window.location.href = 'colisclient.php';
    </script>";
    exit;
}
?>
