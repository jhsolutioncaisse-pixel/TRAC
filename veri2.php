<?php
// ========================= CONNEXION BASE CLEVERCLOUD =========================
$host    = "bmxwrvykt5gv0y88jvj3-mysql.services.clever-cloud.com";
$dbname  = "bmxwrvykt5gv0y88jvj3";
$user    = "usm9pm3hnlnhmoee";
$pass    = "5un1mBwofPvYnS36hOLi";
$port    = 3306;

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// ========================= RECHERCHE =========================
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$colis = [];

// ========================= PAGINATION =========================
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$limit = 50;
$offset = ($page - 1) * $limit;

$totalResultats = 0;
$totalPages = 1;

// ========================= EXECUTION REQUETE =========================
if ($search !== '') {

    $likeSearch = "%$search%";

    // ========================= TOTAL RESULTATS =========================
    $countSql = "
        SELECT COUNT(*) as total
        FROM import_excel
        WHERE (
            noms LIKE ?
            OR num_suivi LIKE ?
            OR telephone LIKE ?
        )
    ";

    $countStmt = $conn->prepare($countSql);

    if ($countStmt) {

        $countStmt->bind_param(
            "sss",
            $likeSearch,
            $likeSearch,
            $likeSearch
        );

        $countStmt->execute();

        $countResult = $countStmt->get_result();

        if ($countRow = $countResult->fetch_assoc()) {
            $totalResultats = (int)$countRow['total'];
        }

        $countStmt->close();
    }

    // ========================= CALCUL PAGES =========================
    $totalPages = ceil($totalResultats / $limit);

    // ========================= REQUETE PRINCIPALE =========================
    $sql = "
        SELECT
            num_suivi,
            noms,
            marchandises,
            cbm,
            pt,
            etatcolis,
            conteneur,
            date_arrivee,
            qte,
            datekinshasa
        FROM import_excel
        WHERE (
            noms LIKE ?
            OR num_suivi LIKE ?
            OR telephone LIKE ?
        )
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt) {

        $stmt->bind_param(
            "sssii",
            $likeSearch,
            $likeSearch,
            $likeSearch,
            $limit,
            $offset
        );

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
function getEtatClass($etat)
{
    $etat = mb_strtolower($etat);

    if (strpos($etat, "enregistré") !== false) {
        return "etat-enregistre";
    }

    if (strpos($etat, "expédié") !== false) {
        return "etat-expedie";
    }

    if (strpos($etat, "livré") !== false) {
        return "etat-livre";
    }

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
        body {
            background: #f8f9fa;
            font-family: system-ui;
        }

        .navbar {
            background: #0d6efd;
        }

        .main-zone {
            min-height: 100vh;
            padding-top: 100px;
            padding-bottom: 40px;
            display: flex;
            justify-content: center;
        }

        .card-box {
            width: 100%;
            max-width: 700px;
            background: #fff;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .colis-card {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 12px;
            font-size: 15px;
        }

        .etat-enregistre {
            background: #cfe2ff;
        }

        .etat-expedie {
            background: #ffe5b4;
        }

        .etat-livre {
            background: #d1e7dd;
        }

        .etat-autre {
            background: #e2e3e5;
        }

        .pagination-zone {
            margin-top: 25px;
        }

        .badge-total {
            font-size: 14px;
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-dark fixed-top">
        <div class="container">
            <span class="navbar-brand fw-bold">
                <i class="bi bi-truck"></i>
                Recherche Colis
            </span>
        </div>
    </nav>

    <div class="main-zone container">

        <div class="card-box">

            <!-- TITRE -->
            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">
                    Résultats pour :
                    "<?php echo htmlspecialchars($search); ?>"
                </h5>

                <span class="badge bg-primary badge-total">
                    <?php echo $totalResultats; ?> résultat(s)
                </span>

            </div>

            <!-- RESULTATS -->
            <?php if (!empty($colis)): ?>

                <?php foreach ($colis as $c): ?>

                    <div class="colis-card <?php echo getEtatClass($c['etatcolis']); ?>">
                        <div>
                            <b>Nom sur le colis :</b>
                            <?php echo htmlspecialchars($c['noms']); ?>
                        </div>
                        <div>
                            <b>Suivi :</b>
                            <?php echo htmlspecialchars($c['num_suivi']); ?>
                        </div>

                        <div>
                            <b>Marchandises :</b>
                            <?php echo htmlspecialchars($c['marchandises']); ?>
                        </div>

                        <div>
                            <b>CBM :</b>
                            <?php echo htmlspecialchars($c['cbm']); ?>
                        </div>

                        <div>
                            <b>Montant à payer/USD :</b>
                            <?php echo htmlspecialchars($c['pt']); ?>
                        </div>

                        <div>
                            <b>Conteneur :</b>
                            <?php echo htmlspecialchars($c['conteneur']); ?>
                        </div>

                        <div>
                            <b>Date arrivée en chine :</b>
                            <?php echo htmlspecialchars($c['date_arrivee']); ?>
                        </div>

                        <div>
                            <b>État :</b>
                            <?php echo htmlspecialchars($c['etatcolis']); ?>
                        </div>
                         <div>
                            <b>date arrivée à kin :</b>
                            <?php echo htmlspecialchars($c['datekinshasa']); ?>
                        </div>
                         <div>
                            <b>Nombre de colis :</b>
                            <?php echo htmlspecialchars($c['qte']); ?>
                        </div>

                    </div>

                <?php endforeach; ?>

                <!-- PAGINATION -->
                <div class="pagination-zone">

                    <nav>

                        <ul class="pagination justify-content-center flex-wrap">

                            <!-- PRECEDENT -->
                            <?php if ($page > 1): ?>

                                <li class="page-item">

                                    <a class="page-link"
                                        href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>">

                                        Précédent

                                    </a>

                                </li>

                            <?php endif; ?>

                            <!-- NUMEROS -->
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">

                                    <a class="page-link"
                                        href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>">

                                        <?php echo $i; ?>

                                    </a>

                                </li>

                            <?php endfor; ?>

                            <!-- SUIVANT -->
                            <?php if ($page < $totalPages): ?>

                                <li class="page-item">

                                    <a class="page-link"
                                        href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>">

                                        Suivant

                                    </a>

                                </li>

                            <?php endif; ?>

                        </ul>

                    </nav>

                </div>

            <?php else: ?>

                <div class="colis-card etat-autre">

                    Aucun résultat trouvé.

                </div>

            <?php endif; ?>

        </div>

    </div>

</body>

</html>