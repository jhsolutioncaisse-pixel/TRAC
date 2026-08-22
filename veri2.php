<?php

// =========================================================
// CONNEXION BASE DE DONNEES - CLEVER CLOUD
// =========================================================

MYSQL_ADDON_HOST=b9xd1ca5virznhlmzgmt-mysql.services.clever-cloud.com
MYSQL_ADDON_DB=b9xd1ca5virznhlmzgmt
MYSQL_ADDON_USER=usm9pm3hnlnhmoee
MYSQL_ADDON_PASSWORD=5un1mBwofPvYnS36hOLi
MYSQL_ADDON_PORT=20856

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

$conn->set_charset("utf8mb4");


// =========================================================
// RECHERCHE
// =========================================================

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

$colis = [];


// =========================================================
// PAGINATION
// =========================================================

$page = isset($_GET['page'])
    ? (int)$_GET['page']
    : 1;

if ($page < 1) {
    $page = 1;
}

$limit = 50;

$offset = ($page - 1) * $limit;

$totalResultats = 0;
$totalPages = 1;


// =========================================================
// RECHERCHE DANS import_excel2
// =========================================================

if ($search !== '') {

    $likeSearch = "%" . $search . "%";


    // =====================================================
    // TOTAL DES RESULTATS
    // =====================================================

    $countSql = "

        SELECT COUNT(*) AS total

        FROM import_excel2

        WHERE

            nomclient LIKE ?
            OR num_suivi LIKE ?
            OR telephone LIKE ?

    ";

    $countStmt = $conn->prepare($countSql);

    if (!$countStmt) {
        die("Erreur SQL : " . $conn->error);
    }

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


    // =====================================================
    // CALCUL DU NOMBRE DE PAGES
    // =====================================================

    if ($totalResultats > 0) {

        $totalPages = (int)ceil(
            $totalResultats / $limit
        );

    } else {

        $totalPages = 1;

    }


    // =====================================================
    // REQUETE PRINCIPALE
    // =====================================================

    $sql = "

        SELECT

            id,
            nomclient,
            marchandises,
            qte,
            cbm,
            pt,
            num_suivi,
            date_arrivee_chine,
            conteneur,
            telephone,
            etatcolis,
            date_expedition,
            date_probable_kin,
            date_kinshasa

        FROM import_excel2

        WHERE

            nomclient LIKE ?
            OR num_suivi LIKE ?
            OR telephone LIKE ?

        ORDER BY id DESC

        LIMIT ? OFFSET ?

    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Erreur SQL : " . $conn->error);
    }

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


$conn->close();


// =========================================================
// SECURITE AFFICHAGE
// =========================================================

function e($value)
{

    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );

}


// =========================================================
// FORMAT DATE
// =========================================================

function formatDate($date)
{

    if (empty($date)) {

        return "-";

    }

    $timestamp = strtotime($date);

    if ($timestamp === false) {

        return e($date);

    }

    return date(
        "d/m/Y",
        $timestamp
    );

}


// =========================================================
// FORMAT DATE + HEURE
// =========================================================

function formatDateTime($date)
{

    if (empty($date)) {

        return "-";

    }

    $timestamp = strtotime($date);

    if ($timestamp === false) {

        return e($date);

    }

    return date(
        "d/m/Y H:i",
        $timestamp
    );

}


// =========================================================
// INFORMATIONS SUR L'ETAT
// =========================================================

function getEtatInfo($etat)
{

    $etatNormalise = mb_strtolower(
        trim((string)$etat),
        'UTF-8'
    );


    // -----------------------------------------------------
    // KINSHASA
    // -----------------------------------------------------

    if (
        strpos($etatNormalise, "kinshasa") !== false
    ) {

        return [

            "class" => "etat-kinshasa",

            "icon" => "bi-geo-alt-fill",

            "titre" => "Kinshasa",

            "description" => "Colis arrivé à Kinshasa"

        ];

    }


    // -----------------------------------------------------
    // EXPEDIE
    // -----------------------------------------------------

    if (
        strpos($etatNormalise, "expédié") !== false ||
        strpos($etatNormalise, "expedie") !== false
    ) {

        return [

            "class" => "etat-expedie",

            "icon" => "bi-truck",

            "titre" => "Expédié",

            "description" => "Colis en cours d'expédition"

        ];

    }


    // -----------------------------------------------------
    // PAYE
    // -----------------------------------------------------

    if (
        strpos($etatNormalise, "payé") !== false ||
        strpos($etatNormalise, "paye") !== false
    ) {

        return [

            "class" => "etat-paye",

            "icon" => "bi-credit-card-fill",

            "titre" => "Payé",

            "description" => "Paiement enregistré"

        ];

    }


    // -----------------------------------------------------
    // RETIRE
    // -----------------------------------------------------

    if (
        strpos($etatNormalise, "retiré") !== false ||
        strpos($etatNormalise, "retire") !== false
    ) {

        return [

            "class" => "etat-retire",

            "icon" => "bi-box-seam-fill",

            "titre" => "Retiré",

            "description" => "Colis retiré par le client"

        ];

    }


    // -----------------------------------------------------
    // AUTRE
    // -----------------------------------------------------

    return [

        "class" => "etat-autre",

        "icon" => "bi-info-circle-fill",

        "titre" => !empty($etat)
            ? $etat
            : "Autre",

        "description" => "Informations du colis"

    ];

}

?>

<!DOCTYPE html>

<html lang="fr">

<head>

<meta charset="UTF-8">

<title>
    Résultats colis | JH-TRACK
</title>

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>


<!-- =====================================================
     BOOTSTRAP
===================================================== -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<!-- =====================================================
     BOOTSTRAP ICONS
===================================================== -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    rel="stylesheet"
>


<style>
*{box-sizing:border-box}
body{margin:0;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#f4f7fb;color:#212529;overflow-x:hidden;font-size:16px}
.navbar{background:linear-gradient(135deg,#0d6efd,#0b5ed7);box-shadow:0 4px 18px rgba(13,110,253,.20)}
.navbar-brand{font-size:19px;letter-spacing:.3px}
.main-zone{min-height:100vh;padding:100px 15px 50px}
.results-container{width:100%;max-width:900px;margin:auto}
.results-header{background:#fff;border-radius:18px;padding:20px 22px;margin-bottom:16px;box-shadow:0 5px 18px rgba(0,0,0,.06);border:1px solid #e6ebf0}
.results-title{font-weight:800;font-size:21px;margin:0;word-break:break-word}.results-title i{color:#0d6efd}.search-value{color:#0d6efd;font-weight:800}.results-subtitle{color:#6c757d;font-size:14px;margin-top:5px}.badge-total{background:#e7f0ff;color:#0d6efd;border-radius:50px;padding:9px 14px;font-size:14px;font-weight:800;white-space:nowrap}
/* UN SEUL BLOC COMPACT PAR RESULTAT */
.colis-card{background:#fff3cd;border:1px solid #ffe69c;border-radius:13px;margin-bottom:12px;padding:15px 16px;box-shadow:0 3px 10px rgba(0,0,0,.045);transition:box-shadow .2s ease,transform .2s ease}.colis-card:hover{box-shadow:0 6px 16px rgba(0,0,0,.08);transform:translateY(-1px)}
.result-number{display:inline-flex;align-items:center;gap:5px;color:#856404;font-size:12px;font-weight:800;margin-bottom:7px}.result-number i{font-size:13px}
/* Toutes les informations sous forme de lignes */
.colis-info-list{display:flex;flex-direction:column;gap:4px}
.colis-line{display:flex;align-items:flex-start;gap:7px;line-height:1.35;font-size:14px;min-width:0}.colis-line>i{width:18px;flex:0 0 18px;text-align:center;color:#856404;margin-top:2px}.colis-line strong{font-weight:800;color:#212529;margin-right:3px}.colis-line span.value{font-weight:500;overflow-wrap:anywhere;word-break:break-word}.colis-line .money-value{font-weight:800;color:#198754}.colis-line .tracking-value{font-weight:800;color:#0d6efd;letter-spacing:.2px}
/* ETAT : icône conservée */
.etat-inline{display:inline-flex;align-items:center;gap:6px;font-weight:800}.etat-icon{width:23px;height:23px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;font-size:12px;flex:0 0 23px}.etat-title{font-weight:800}.etat-description{font-size:12px;font-weight:500;opacity:.75;margin-left:3px}
.etat-kinshasa{color:#856404}.etat-kinshasa .etat-icon{background:#ffc107;color:#212529}.etat-expedie{color:#084298}.etat-expedie .etat-icon{background:#0d6efd;color:#fff}.etat-paye{color:#7a4100}.etat-paye .etat-icon{background:#fd7e14;color:#fff}.etat-retire{color:#842029}.etat-retire .etat-icon{background:#dc3545;color:#fff}.etat-autre{color:#164e63}.etat-autre .etat-icon{background:#6c9bd2;color:#fff}
/* Ligne des dates */
.dates-separator{height:1px;background:rgba(133,100,4,.20);margin:8px 0 5px}.dates-title{display:flex;align-items:center;gap:7px;font-size:13px;font-weight:800;color:#856404;margin-bottom:4px}.dates-title i{color:#856404}.dates-list{display:flex;flex-direction:column;gap:4px}
/* Pagination */
.pagination-zone{background:#fff;border-radius:16px;padding:15px;margin-top:18px;box-shadow:0 5px 18px rgba(0,0,0,.05)}.pagination{margin:0;gap:5px;flex-wrap:wrap}.page-link{border-radius:8px!important;border:1px solid #dee2e6;color:#0d6efd;font-weight:700;font-size:14px}.page-item.active .page-link{background:#0d6efd;border-color:#0d6efd}
.no-result{background:#fff;border-radius:18px;padding:45px 25px;text-align:center;box-shadow:0 7px 22px rgba(0,0,0,.06)}.no-result-icon{width:70px;height:70px;border-radius:50%;margin:0 auto 17px;display:flex;align-items:center;justify-content:center;background:#e7f0ff;color:#0d6efd;font-size:30px}.no-result h5{font-size:21px;font-weight:800}.no-result p{color:#6c757d;font-size:16px}
@media(max-width:700px){.main-zone{padding:88px 10px 40px}.results-header{padding:16px;border-radius:15px}.results-title{font-size:18px}.results-subtitle{font-size:13px}.badge-total{font-size:12px;padding:7px 10px}.colis-card{padding:13px 14px;border-radius:12px}.colis-line{font-size:13px}.result-number{font-size:11px}.dates-title{font-size:12px}.etat-description{display:block;margin-left:29px}.etat-inline{flex-wrap:wrap}.colis-line strong{margin-right:2px}}
@media(max-width:400px){body{font-size:15px}.results-title{font-size:17px}.colis-card{padding:12px}.colis-line{font-size:12.5px;gap:5px}.colis-line>i{width:17px;flex-basis:17px}.etat-icon{width:21px;height:21px;flex-basis:21px;font-size:11px}}
</style>

</head>


<body>


<!-- =========================================================
     NAVBAR
========================================================= -->

<nav class="navbar navbar-dark fixed-top">

    <div class="container">

        <div class="d-flex align-items-center">

            <a
                href="VERIFI_maritime.html"
                class="btn btn-link text-white p-1 me-2"
                title="Retour"
            >

                <i
                    class="bi bi-arrow-left fs-4"
                ></i>

            </a>


            <span class="navbar-brand fw-bold mb-0">

                <i class="bi bi-truck"></i>

                JH-TRACK

            </span>

        </div>

    </div>

</nav>


<!-- =========================================================
     CONTENU
========================================================= -->

<main class="main-zone">

<div class="results-container">


<!-- =====================================================
     ENTETE RESULTATS
====================================================== -->

<div class="results-header">

    <div
        class="d-flex justify-content-between align-items-center gap-3"
    >

        <div>

            <div class="results-title">

                <i class="bi bi-search"></i>

                Résultats pour :

                <span class="search-value">

                    "<?php echo e($search); ?>"

                </span>

            </div>


            <div class="results-subtitle">

                Recherche par nom du client,
                numéro de suivi ou téléphone.

            </div>

        </div>


        <span class="badge-total">

            <i class="bi bi-box-seam-fill me-1"></i>

            <?php echo $totalResultats; ?>

            résultat(s)

        </span>

    </div>

</div>


<!-- =====================================================
     RESULTATS
====================================================== -->

<?php if (!empty($colis)): ?>


<?php foreach ($colis as $index => $c): ?>


<?php

$etat = getEtatInfo(
    $c['etatcolis']
);


/*
 * Numéro global du résultat.
 *
 * Exemple :
 * page 1 :
 * Résultat 1
 * Résultat 2
 *
 * page 2 :
 * Résultat 51
 * Résultat 52
 */

$resultatNumero =
    $offset + $index + 1;

?>


<!-- =================================================
     RESULTAT : UN SEUL BLOC COMPACT
================================================== -->

<div class="colis-card">

    <div class="result-number">
        <i class="bi bi-hash"></i>
        Résultat <?php echo $resultatNumero; ?>
    </div>

    <div class="colis-info-list">

        <!-- NOM CLIENT -->
        <div class="colis-line">
            <i class="bi bi-person-fill"></i>
            <div>
                <strong>Nom sur le colis :</strong>
                <span class="value"><?php echo e($c['nomclient']); ?></span>
            </div>
        </div>

        <!-- NUMERO DE SUIVI -->
        <div class="colis-line">
            <i class="bi bi-upc-scan"></i>
            <div>
                <strong>Suivi :</strong>
                <span class="value tracking-value">
                    <?php echo !empty($c['num_suivi']) ? e($c['num_suivi']) : '-'; ?>
                </span>
            </div>
        </div>

        <!-- MARCHANDISE -->
        <div class="colis-line">
            <i class="bi bi-box-seam-fill"></i>
            <div>
                <strong>Marchandises :</strong>
                <span class="value"><?php echo e($c['marchandises']); ?></span>
            </div>
        </div>

        <!-- QUANTITE -->
        <div class="colis-line">
            <i class="bi bi-123"></i>
            <div>
                <strong>Nombre de colis :</strong>
                <span class="value"><?php echo e($c['qte']); ?></span>
            </div>
        </div>

        <!-- CBM -->
        <div class="colis-line">
            <i class="bi bi-boxes"></i>
            <div>
                <strong>CBM :</strong>
                <span class="value"><?php echo e($c['cbm']); ?></span>
            </div>
        </div>

        <!-- MONTANT -->
        <div class="colis-line">
            <i class="bi bi-cash-stack"></i>
            <div>
                <strong>Montant à payer/USD :</strong>
                <span class="value money-value"><?php echo e($c['pt']); ?></span>
            </div>
        </div>

        <!-- CONTENEUR -->
        <div class="colis-line">
            <i class="bi bi-box-fill"></i>
            <div>
                <strong>Conteneur :</strong>
                <span class="value">
                    <?php echo !empty($c['conteneur']) ? e($c['conteneur']) : '-'; ?>
                </span>
            </div>
        </div>

        <!-- TELEPHONE -->
        <div class="colis-line">
            <i class="bi bi-telephone-fill"></i>
            <div>
                <strong>Téléphone :</strong>
                <span class="value">
                    <?php echo !empty($c['telephone']) ? e($c['telephone']) : '-'; ?>
                </span>
            </div>
        </div>

        <!-- DATE ARRIVEE CHINE -->
        <div class="colis-line">
            <i class="bi bi-geo-alt-fill"></i>
            <div>
                <strong>Date arrivée en Chine :</strong>
                <span class="value"><?php echo formatDate($c['date_arrivee_chine']); ?></span>
            </div>
        </div>

        <!-- ETAT AVEC ICON -->
        <div class="colis-line <?php echo e($etat['class']); ?>">
            <i class="bi bi-info-circle-fill"></i>
            <div>
                <strong>État :</strong>
                <span class="etat-inline">
                    <span class="etat-icon">
                        <i class="bi <?php echo e($etat['icon']); ?>"></i>
                    </span>
                    <span class="etat-title"><?php echo e($etat['titre']); ?></span>
                    <span class="etat-description"><?php echo e($etat['description']); ?></span>
                </span>
            </div>
        </div>

        <div class="dates-separator"></div>

        <div class="dates-title">
            <i class="bi bi-calendar-event-fill"></i>
            Suivi des dates
        </div>

        <div class="dates-list">

            <!-- DATE EXPEDITION -->
            <div class="colis-line">
                <i class="bi bi-truck"></i>
                <div>
                    <strong>Date expédition :</strong>
                    <span class="value"><?php echo formatDateTime($c['date_expedition']); ?></span>
                </div>
            </div>

            <!-- DATE PROBABLE -->
            <div class="colis-line">
                <i class="bi bi-calendar-check-fill"></i>
                <div>
                    <strong>Date probable à Kin :</strong>
                    <span class="value"><?php echo formatDate($c['date_probable_kin']); ?></span>
                </div>
            </div>

            <!-- DATE KINSHASA -->
            <div class="colis-line">
                <i class="bi bi-geo-alt-fill"></i>
                <div>
                    <strong>Date arrivée à Kin :</strong>
                    <span class="value"><?php echo formatDateTime($c['date_kinshasa']); ?></span>
                </div>
            </div>

        </div>

    </div>

</div>

<?php endforeach; ?>


<!-- =================================================
     PAGINATION
================================================== -->

<?php if ($totalPages > 1): ?>


<div class="pagination-zone">

<nav aria-label="Pagination">

<ul class="pagination justify-content-center">


<!-- PRECEDENT -->

<?php if ($page > 1): ?>

<li class="page-item">

<a
    class="page-link"
    href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>"
>

    <i class="bi bi-chevron-left"></i>

    <span class="d-none d-sm-inline">

        Précédent

    </span>

</a>

</li>

<?php endif; ?>


<?php

$startPage = max(
    1,
    $page - 2
);

$endPage = min(
    $totalPages,
    $page + 2
);

?>


<!-- PREMIERE PAGE -->

<?php if ($startPage > 1): ?>

<li class="page-item">

<a
    class="page-link"
    href="?search=<?php echo urlencode($search); ?>&page=1"
>

    1

</a>

</li>


<?php if ($startPage > 2): ?>

<li class="page-item disabled">

<span class="page-link">

    ...

</span>

</li>

<?php endif; ?>

<?php endif; ?>


<!-- NUMEROS -->

<?php

for (
    $i = $startPage;
    $i <= $endPage;
    $i++
):

?>

<li
    class="page-item
    <?php
    echo (
        $i == $page
    )
        ? 'active'
        : '';
    ?>"
>

<a
    class="page-link"
    href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"
>

    <?php echo $i; ?>

</a>

</li>

<?php endfor; ?>


<!-- DERNIERE PAGE -->

<?php if ($endPage < $totalPages): ?>


<?php if (
    $endPage < $totalPages - 1
): ?>

<li class="page-item disabled">

<span class="page-link">

    ...

</span>

</li>

<?php endif; ?>


<li class="page-item">

<a
    class="page-link"
    href="?search=<?php echo urlencode($search); ?>&page=<?php echo $totalPages; ?>"
>

    <?php echo $totalPages; ?>

</a>

</li>


<?php endif; ?>


<!-- SUIVANT -->

<?php if ($page < $totalPages): ?>

<li class="page-item">

<a
    class="page-link"
    href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>"
>

    <span class="d-none d-sm-inline">

        Suivant

    </span>

    <i class="bi bi-chevron-right"></i>

</a>

</li>

<?php endif; ?>


</ul>

</nav>

</div>

<?php endif; ?>


<?php else: ?>


<!-- =================================================
     AUCUN RESULTAT
================================================== -->

<div class="no-result">

    <div class="no-result-icon">

        <i class="bi bi-search"></i>

    </div>


    <h5>

        Aucun résultat

    </h5>


    <p>

        Aucun colis ne correspond à :

    </p>


    <p>

        <strong>

            "<?php echo e($search); ?>"

        </strong>

    </p>

</div>


<?php endif; ?>


</div>

</main>


<!-- =========================================================
     BOOTSTRAP JS
========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>
