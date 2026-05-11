<?php
// ========================= CONNEXION BASE CLEVERCLOUD =========================
$host    = "bmxwrvykt5gv0y88jvj3-mysql.services.clever-cloud.com";
$dbname  = "bmxwrvykt5gv0y88jvj3";
$user    = "usm9pm3hnlnhmoee";
$pass    = "5un1mBwofPvYnS36hOLi"; // ⚠️ idéalement mettre dans .env
$port    = 3306;

// Connexion
$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// ========================= RECHERCHE =========================
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$colis = [];

if ($search !== '') {

    $stmt = $conn->prepare("
        SELECT num_suivi, marchandises, cbm, pt, etatcolis, conteneur, date_arrivee
        FROM import_excel
        WHERE (noms LIKE ? OR num_suivi LIKE ? OR telephone LIKE ?)
        ORDER BY created_at ASC
    ");

    if ($stmt) {

        $likeSearch = "%$search%";
        $stmt->bind_param("sss", $likeSearch, $likeSearch, $likeSearch);
        $stmt->execute();

        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $colis[] = $row;
        }

        $stmt->close();
    }
}

$conn->close();

// ========================= ETAT COULEUR =========================
function getEtatClass($etat) {
    $etat = mb_strtolower($etat);

    if (strpos($etat, "enregistré") !== false) return "etat-enregistre";
    if (strpos($etat, "expédié") !== false) return "etat-expedie";
    if (strpos($etat, "livré") !== false) return "etat-livre";
    return "etat-autre";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats Colis</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background:#f8f9fa; font-family: system-ui; }
        .navbar { background:#0d6efd; }

        .main-zone {
            min-height: 100vh;
            padding-top: 100px;
            display:flex;
            justify-content:center;
        }

        .card-box {
            width:100%;
            max-width:650px;
            background:#fff;
            padding:25px;
            border-radius:15px;
            box-shadow:0 10px 30px rgba(0,0,0,0.1);
        }

        .colis-card {
            padding:12px;
            border-radius:10px;
            margin-bottom:10px;
        }

        .etat-enregistre { background:#cfe2ff; }
        .etat-expedie { background:#ffe5b4; }
        .etat-livre { background:#d1e7dd; }
        .etat-autre { background:#e2e3e5; }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark fixed-top">
    <div class="container">
        <span class="navbar-brand fw-bold">
            <i class="bi bi-truck"></i> Recherche Colis
        </span>
    </div>
</nav>

<div class="main-zone container">
    <div class="card-box">

        <h5 class="text-center mb-3">
            Résultats pour "<?php echo htmlspecialchars($search); ?>"
        </h5>

        <?php if (!empty($colis)): ?>

            <?php foreach ($colis as $c): ?>
                <div class="colis-card <?php echo getEtatClass($c['etatcolis']); ?>">

                    <b>Suivi :</b> <?php echo htmlspecialchars($c['num_suivi']); ?><br>
                    <b>Marchandises :</b> <?php echo htmlspecialchars($c['marchandises']); ?><br>
                    <b>CBM :</b> <?php echo htmlspecialchars($c['cbm']); ?><br>
                    <b>Montant à payer/USD :</b> <?php echo htmlspecialchars($c['pt']); ?><br>
                    <b>Conteneur :</b> <?php echo htmlspecialchars($c['conteneur']); ?><br>
                    <b>Date arrivée en chine:</b> <?php echo htmlspecialchars($c['date_arrivee']); ?><br>
                    <b>État :</b> <?php echo htmlspecialchars($c['etatcolis']); ?>

                </div>
            <?php endforeach; ?>

        <?php else: ?>

            <div class="colis-card etat-autre">
                Aucun résultat trouvé.
            </div>

        <?php endif; ?>

    </div>
</div>

</body>
</html>