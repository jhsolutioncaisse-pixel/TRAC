<?php

session_start();


/* =====================================================
   PROTECTION SESSION
===================================================== */

if (!isset($_SESSION['codeclient'])) {

    header("Location: compte.html");
    exit;

}


/* =====================================================
   CONNEXION A L'ANCIENNE BASE
   ON NE CHANGE PAS CETTE BASE
===================================================== */

$host   = "b9xd1ca5virznhlmzgmt-mysql.services.clever-cloud.com";
$dbname = "b9xd1ca5virznhlmzgmt";
$user   = "usm9pm3hnlnhmoee";
$pass   = "5un1mBwofPvYnS36hOLi";
$port   = 20856;


try {

    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 10
        ]
    );

} catch (PDOException $e) {

    http_response_code(500);

    die("Erreur de connexion à la base de données.");

}


/* =====================================================
   SESSION CLIENT
===================================================== */

$Nomclient = $_SESSION['Nomclient'] ?? '';

$telephone = $_SESSION['telephone'] ?? '';

$mail = $_SESSION['mail'] ?? '';

$codeclient = $_SESSION['codeclient'] ?? '';


/* =====================================================
   NORMALISATION
===================================================== */

function normalize($str)
{
    $str = (string)$str;

    $str = strtolower($str);

    $str = trim($str);

    $str = preg_replace('/\s+/', ' ', $str);

    return $str;
}


$nomClean = normalize($Nomclient);


/* =====================================================
   PAGINATION
===================================================== */

$limit = 12;

$page = isset($_GET['page'])
    ? max(1, intval($_GET['page']))
    : 1;

$offset = ($page - 1) * $limit;


/* =====================================================
   RECHERCHE DES COLIS
===================================================== */

/*
   Le téléphone est le critère principal.

   Le nom est utilisé comme critère secondaire.
*/

$where = "
(
    telephone = :tel
    OR LOWER(TRIM(noms)) LIKE :nom
)
";


/* =====================================================
   TOTAL DES COLIS
===================================================== */

try {

    $count = $pdo->prepare("
        SELECT COUNT(*)
        FROM import_excel
        WHERE $where
    ");

    $count->execute([
        ':tel' => $telephone,
        ':nom' => "%{$nomClean}%"
    ]);

    $total = (int)$count->fetchColumn();

} catch (PDOException $e) {

    http_response_code(500);

    die("Erreur lors du comptage des colis.");

}


/* =====================================================
   NOMBRE DE PAGES
===================================================== */

$totalPages = ($total > 0)
    ? (int)ceil($total / $limit)
    : 0;


/* =====================================================
   RECUPERATION DES COLIS
===================================================== */

try {

    $sql = "
        SELECT *
        FROM import_excel
        WHERE $where
        ORDER BY created_at DESC
        LIMIT {$limit} OFFSET {$offset}
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':tel' => $telephone,
        ':nom' => "%{$nomClean}%"
    ]);

    $colis = $stmt->fetchAll();

} catch (PDOException $e) {

    http_response_code(500);

    die("Erreur lors de la récupération des colis.");

}


/* =====================================================
   FONCTION ECHAPPEMENT HTML
===================================================== */

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<title>JH-TRACK | Dashboard Client</title>

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    rel="stylesheet"
>


<style>

/* =====================================================
   GENERAL
===================================================== */

body {

    background: #eef3ff;

    font-family: system-ui, -apple-system, BlinkMacSystemFont,
                 "Segoe UI", sans-serif;

}


/* =====================================================
   NAVBAR
===================================================== */

.navbar-custom {

    background: linear-gradient(
        135deg,
        #0d6efd,
        #0047b3
    );

    box-shadow:
        0 4px 15px rgba(0,0,0,.10);

}


/* =====================================================
   HERO
===================================================== */

.hero-box {

    background: white;

    border-radius: 24px;

    padding: 30px;

    box-shadow:
        0 10px 35px rgba(0,0,0,.08);

}


/* =====================================================
   CARD COLIS
===================================================== */

.card-colis {

    border: none;

    border-radius: 22px;

    overflow: hidden;

    background: white;

    box-shadow:
        0 10px 30px rgba(0,0,0,.08);

    transition: .25s;

    height: 100%;

}


.card-colis:hover {

    transform: translateY(-5px);

}


/* =====================================================
   TOP CARD
===================================================== */

.card-top {

    background: linear-gradient(
        135deg,
        #0d6efd,
        #0056d6
    );

    color: white;

    padding: 18px;

}


/* =====================================================
   INFORMATIONS
===================================================== */

.info-line {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 15px;

    margin-bottom: 12px;

    font-size: .94rem;

}


.label {

    color: #6c757d;

    font-weight: 600;

}


.value {

    font-weight: 700;

    text-align: right;

    max-width: 60%;

    word-break: break-word;

}


/* =====================================================
   STATUS
===================================================== */

.badge-status {

    font-size: .75rem;

    padding: 8px 12px;

    border-radius: 30px;

}


/* =====================================================
   PAGINATION
===================================================== */

.pagination .page-link {

    border: none;

    margin: 0 4px;

    border-radius: 12px;

    color: #0d6efd;

    font-weight: 600;

}


.pagination .active .page-link {

    background: #0d6efd;

    color: white;

}


/* =====================================================
   EMPTY
===================================================== */

.empty-box {

    background: white;

    border-radius: 20px;

    padding: 50px;

    box-shadow:
        0 8px 25px rgba(0,0,0,.06);

}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 576px) {

    .hero-box {

        padding: 20px;

        border-radius: 18px;

    }

    .hero-box h2 {

        font-size: 1.35rem;

    }

    .info-line {

        font-size: .88rem;

    }

    .value {

        max-width: 55%;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">

    <div class="container">


        <a
            class="navbar-brand fw-bold"
            href="#"
        >

            <i class="bi bi-box-seam-fill"></i>

            JH-TRACK

        </a>


        <div class="ms-auto text-white text-end">

            <div class="fw-bold">

                <i class="bi bi-person-circle"></i>

                <?= e($Nomclient) ?>

            </div>

            <small>

                <?= e($mail) ?>

            </small>

        </div>


        <a
            href="bima.php"
            class="btn btn-light ms-3 rounded-pill px-4"
        >

            <i class="bi bi-box-arrow-right"></i>

            Déconnexion

        </a>

    </div>

</nav>



<!-- =====================================================
     CONTAINER
===================================================== -->

<div class="container py-4">


    <!-- =================================================
         HERO
    ================================================= -->

    <div class="hero-box mb-4">

        <div class="row align-items-center">


            <div class="col-lg-8">

                <h2 class="fw-bold mb-2">

                    Bienvenue
                    ( <?= e($Nomclient) ?> ) !

                </h2>


                <p class="text-muted mb-2">

                    L'affichage de vos colis est principalement
                    lié à votre numéro de téléphone.

                </p>


                <?php if ($telephone !== ''): ?>

                    <p class="mb-0">

                        <strong>

                            <i class="bi bi-telephone-fill text-primary"></i>

                            Téléphone associé :

                        </strong>

                        <?= e($telephone) ?>

                    </p>

                <?php endif; ?>


                <p class="text-muted mt-2 mb-0">

                    Si un colis ne s'affiche pas ici,
                    veuillez vérifier l'onglet consulter
                    ou contactez-nous.

                </p>

            </div>


            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">

                <div
                    class="d-inline-block bg-primary text-white rounded-4 px-4 py-3"
                >

                    <div class="small">

                        COLIS TROUVÉS

                    </div>


                    <div class="fs-3 fw-bold">

                        <?= $total ?>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- =================================================
         LISTE COLIS
    ================================================= -->

    <div class="row g-4">


        <?php if ($total === 0): ?>


            <div class="col-12">

                <div class="empty-box text-center">

                    <i
                        class="bi bi-inbox display-1 text-primary"
                    ></i>


                    <h4 class="mt-3 fw-bold">

                        Aucun colis trouvé

                    </h4>


                    <p class="text-muted">

                        Aucun colis n'est actuellement
                        associé à votre compte.

                    </p>


                    <?php if ($telephone !== ''): ?>

                        <p class="small text-muted">

                            Recherche effectuée avec le numéro :

                            <strong>

                                <?= e($telephone) ?>

                            </strong>

                        </p>

                    <?php endif; ?>

                </div>

            </div>


        <?php endif; ?>



        <?php foreach ($colis as $c): ?>


            <div class="col-md-6 col-xl-4">

                <div class="card-colis">


                    <!-- =================================
                         TOP
                    ================================== -->

                    <div
                        class="card-top d-flex justify-content-between align-items-center"
                    >

                        <div>

                            <div class="small opacity-75">

                                COLIS

                            </div>


                            <div class="fw-bold fs-5">

                                #<?= e($c['id'] ?? '') ?>

                            </div>

                        </div>


                        <span
                            class="badge bg-light text-success badge-status"
                        >

                            <?= e($c['etatcolis'] ?? '') ?>

                        </span>

                    </div>



                    <!-- =================================
                         BODY
                    ================================== -->

                    <div class="p-4">


                        <!-- NOM -->

                        <div class="info-line">

                            <div class="label">

                                <i class="bi bi-person"></i>

                                Noms

                            </div>


                            <div class="value">

                                <?= e($c['noms'] ?? '') ?>

                            </div>

                        </div>



                        <!-- MARCHANDISE -->

                        <div class="info-line">

                            <div class="label">

                                <i class="bi bi-box"></i>

                                Marchandise

                            </div>


                            <div class="value">

                                <?= e($c['marchandises'] ?? '') ?>

                            </div>

                        </div>



                        <!-- QUANTITE -->

                        <div class="info-line">

                            <div class="label">

                                <i class="bi bi-123"></i>

                                Quantité

                            </div>


                            <div class="value">

                                <?= e($c['qte'] ?? '') ?>

                            </div>

                        </div>



                        <!-- CBM -->

                        <div class="info-line">

                            <div class="label">

                                <i class="bi bi-rulers"></i>

                                CBM

                            </div>


                            <div class="value">

                                <?= e($c['cbm'] ?? '') ?>

                            </div>

                        </div>



                        <!-- MONTANT -->

                        <div class="info-line">

                            <div class="label">

                                <i class="bi bi-cash-stack"></i>

                                Montant

                            </div>


                            <div class="value text-primary">

                                <?= e($c['pt'] ?? '') ?>

                                $

                            </div>

                        </div>



                        <hr>



                        <!-- NUMERO SUIVI -->

                        <div class="info-line">

                            <div class="label">

                                <i class="bi bi-upc-scan"></i>

                                N° Suivi

                            </div>


                            <div class="value">

                                <?= e($c['num_suivi'] ?? '') ?>

                            </div>

                        </div>



                        <!-- TELEPHONE -->

                        <div class="info-line">

                            <div class="label">

                                <i class="bi bi-telephone"></i>

                                Téléphone

                            </div>


                            <div class="value">

                                <?= e($c['telephone'] ?? '') ?>

                            </div>

                        </div>



                        <!-- ARRIVEE CHINE -->

                        <div class="info-line">

                            <div class="label">

                                <i class="bi bi-calendar-event"></i>

                                Arrivée Chine

                            </div>


                            <div class="value">

                                <?= e($c['date_arrivee'] ?? '') ?>

                            </div>

                        </div>



                        <!-- ARRIVEE KINSHASA -->

                        <div class="info-line">

                            <div class="label">

                                <i class="bi bi-calendar-check"></i>

                                Arrivé à Kinshasa

                            </div>


                            <div class="value">

                                <?= e($c['datekinshasa'] ?? '') ?>

                            </div>

                        </div>


                    </div>

                </div>

            </div>


        <?php endforeach; ?>


    </div>



    <!-- =================================================
         PAGINATION
    ================================================= -->

    <?php if ($totalPages > 1): ?>


        <nav class="mt-5">

            <ul
                class="pagination justify-content-center flex-wrap"
            >


                <?php for (
                    $i = 1;
                    $i <= $totalPages;
                    $i++
                ): ?>


                    <li
                        class="page-item
                        <?= ($i === $page ? 'active' : '') ?>"
                    >

                        <a
                            class="page-link px-3 py-2"
                            href="?page=<?= $i ?>"
                        >

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

