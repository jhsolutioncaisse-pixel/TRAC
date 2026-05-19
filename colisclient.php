<?php
session_start();

/* =========================
   PROTECTION SESSION
========================= */
if (!isset($_SESSION['codeclient'])) {
    header("Location: login.html");
    exit;
}

/* =========================
   CONNEXION DB
========================= */
try {

    $pdo = new PDO(
        "mysql:host=bmxwrvykt5gv0y88jvj3-mysql.services.clever-cloud.com;
        dbname=bmxwrvykt5gv0y88jvj3;
        charset=utf8",
        "usm9pm3hnlnhmoee",
        "5un1mBwofPvYnS36hOLi",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

} catch (Exception $e) {
    die("Erreur de connexion");
}

/* =========================
   SESSION CLIENT
========================= */
$Nomclient = $_SESSION['Nomclient'] ?? '';
$telephone = $_SESSION['telephone'] ?? '';
$mail      = $_SESSION['mail'] ?? '';

/* =========================
   NORMALISATION NOM
========================= */
function normalize($str)
{
    $str = strtolower($str);
    $str = trim($str);
    $str = preg_replace('/\s+/', ' ', $str);
    return $str;
}

$nomClean = normalize($Nomclient);

/* =========================
   PAGINATION
========================= */
$limit = 12;

$page = isset($_GET['page'])
    ? max(1, intval($_GET['page']))
    : 1;

$offset = ($page - 1) * $limit;

/* =========================
   CONDITIONS
========================= */
$where = "
(
    telephone = :tel
    OR LOWER(noms) LIKE :nom
)
";

/* =========================
   TOTAL
========================= */
$count = $pdo->prepare("
    SELECT COUNT(*)
    FROM import_excel
    WHERE $where
");

$count->execute([
    ':tel' => $telephone,
    ':nom' => "%$nomClean%"
]);

$total = $count->fetchColumn();

$totalPages = ceil($total / $limit);

/* =========================
   DATA
========================= */
$sql = "
SELECT *
FROM import_excel
WHERE $where
ORDER BY created_at DESC
LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':tel' => $telephone,
    ':nom' => "%$nomClean%"
]);

$colis = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<title>JH-TRACK | Dashboard Client</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>

body{
    background:#eef3ff;
    font-family:system-ui;
}

/* =========================
   NAVBAR
========================= */
.navbar-custom{
    background:linear-gradient(135deg,#0d6efd,#0047b3);
    box-shadow:0 4px 15px rgba(0,0,0,.1);
}

/* =========================
   HEADER DASHBOARD
========================= */
.hero-box{
    background:white;
    border-radius:24px;
    padding:30px;
    box-shadow:0 10px 35px rgba(0,0,0,.08);
}

/* =========================
   CARD COLIS
========================= */
.card-colis{
    border:none;
    border-radius:22px;
    overflow:hidden;
    background:white;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
    transition:.25s;
    height:100%;
}

.card-colis:hover{
    transform:translateY(-5px);
}

.card-top{
    background:linear-gradient(135deg,#0d6efd,#0056d6);
    color:white;
    padding:18px;
}

.info-line{
    display:flex;
    justify-content:space-between;
    margin-bottom:12px;
    font-size:.94rem;
}

.label{
    color:#6c757d;
    font-weight:600;
}

.value{
    font-weight:700;
    text-align:right;
}

/* =========================
   STATUS
========================= */
.badge-status{
    font-size:.75rem;
    padding:8px 12px;
    border-radius:30px;
}

/* =========================
   PAGINATION
========================= */
.pagination .page-link{
    border:none;
    margin:0 4px;
    border-radius:12px;
    color:#0d6efd;
    font-weight:600;
}

.pagination .active .page-link{
    background:#0d6efd;
}

/* =========================
   EMPTY
========================= */
.empty-box{
    background:white;
    border-radius:20px;
    padding:50px;
    box-shadow:0 8px 25px rgba(0,0,0,.06);
}

</style>

</head>

<body>

<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">

<div class="container">

<a class="navbar-brand fw-bold" href="#">
<i class="bi bi-box-seam-fill"></i>
JH-TRACK
</a>

<div class="ms-auto text-white text-end">

<div class="fw-bold">
<i class="bi bi-person-circle"></i>
<?= htmlspecialchars($Nomclient) ?>
</div>

<small>
<?= htmlspecialchars($mail) ?>
</small>

</div>

<a href="bima.php" class="btn btn-light ms-3 rounded-pill px-4">
<i class="bi bi-box-arrow-right"></i>
Déconnexion
</a>

</div>

</nav>

<!-- =========================
     CONTAINER
========================= -->

<div class="container py-4">

<!-- HERO -->

<div class="hero-box mb-4">

<div class="row align-items-center">

<div class="col-lg-8">

<h2 class="fw-bold mb-2">
Bienvenue <?= htmlspecialchars($Nomclient) ?>
</h2>

<p class="text-muted mb-0">
Suivez vos colis en temps réel depuis votre espace client sécurisé.
</p>

</div>

<div class="col-lg-4 text-lg-end mt-3 mt-lg-0">

<div class="d-inline-block bg-primary text-white rounded-4 px-4 py-3">

<div class="small">
TOTAL COLIS
</div>

<div class="fs-3 fw-bold">
<?= $total ?>
</div>

</div>

</div>

</div>

</div>

<!-- =========================
     LISTE COLIS
========================= -->

<div class="row g-4">

<?php if($total == 0): ?>

<div class="col-12">

<div class="empty-box text-center">

<i class="bi bi-inbox display-1 text-primary"></i>

<h4 class="mt-3 fw-bold">
Aucun colis trouvé
</h4>

<p class="text-muted">
Aucun colis n'est actuellement associé à votre compte.
</p>

</div>

</div>

<?php endif; ?>

<?php foreach($colis as $c): ?>

<div class="col-md-6 col-xl-4">

<div class="card-colis">

<!-- TOP -->

<div class="card-top d-flex justify-content-between align-items-center">

<div>

<div class="small opacity-75">
COLIS
</div>

<div class="fw-bold fs-5">
#<?= htmlspecialchars($c['id']) ?>
</div>

</div>

<span class="badge bg-light text-success badge-status">

<?= htmlspecialchars($c['etatcolis']) ?>

</span>

</div>

<!-- BODY -->

<div class="p-4">

<div class="info-line">
<div class="label">
<i class="bi bi-box"></i>
Marchandise
</div>

<div class="value">
<?= htmlspecialchars($c['marchandises']) ?>
</div>
</div>

<div class="info-line">
<div class="label">
<i class="bi bi-123"></i>
Quantité
</div>

<div class="value">
<?= htmlspecialchars($c['qte']) ?>
</div>
</div>

<div class="info-line">
<div class="label">
<i class="bi bi-rulers"></i>
CBM
</div>

<div class="value">
<?= htmlspecialchars($c['cbm']) ?>
</div>
</div>

<div class="info-line">
<div class="label">
<i class="bi bi-cash-stack"></i>
Montant
</div>

<div class="value text-primary">
<?= htmlspecialchars($c['pt']) ?>
$
</div>
</div>

<hr>

<div class="info-line">
<div class="label">
<i class="bi bi-upc-scan"></i>
N° Suivi
</div>

<div class="value">
<?= htmlspecialchars($c['num_suivi']) ?>
</div>
</div>

<div class="info-line">
<div class="label">
<i class="bi bi-calendar-event"></i>
Arrivée Chine le
</div>

<div class="value">
<?= htmlspecialchars($c['date_arrivee']) ?>
</div>
</div>

<div class="info-line">
<div class="label">
<i class="bi bi-calendar-check"></i>
Arrivé à Kinshasa le
</div>

<div class="value">
<?= htmlspecialchars($c['datekinshasa']) ?>
</div>
</div>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

<!-- =========================
     PAGINATION
========================= -->

<?php if($totalPages > 1): ?>

<nav class="mt-5">

<ul class="pagination justify-content-center flex-wrap">

<?php for($i = 1; $i <= $totalPages; $i++): ?>

<li class="page-item <?= ($i == $page ? 'active' : '') ?>">

<a class="page-link px-3 py-2" href="?page=<?= $i ?>">

<?= $i ?>

</a>

</li>

<?php endfor; ?>

</ul>

</nav>

<?php endif; ?>

</div>

</body>
</html>