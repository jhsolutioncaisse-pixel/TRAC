<?php
// Page d'accueil simple
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>JH SOLUTION</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Icône -->
    <link rel="icon" type="image/png" href="iconjh-192.png">

    <style>

        body{
            height:100vh;
            margin:0;
            background:#ffffff;
            display:flex;
            justify-content:center;
            align-items:center;
            font-family:system-ui;
        }

        .splash{
            text-align:center;
        }

        .logo{
            width:220px;
            animation:pulse 2.5s infinite;
        }

        .btn-connect{
            padding:12px 30px;
            font-size:1.1rem;
            border-radius:50px;
            margin-top:25px;
        }

        @keyframes pulse{
            0%{
                transform:scale(1);
            }
            50%{
                transform:scale(1.04);
            }
            100%{
                transform:scale(1);
            }
        }

    </style>
</head>

<body>

<div class="splash">

    <img src="iconjh-512.png" alt="JH SOLUTION" class="logo mb-4">

    <br>

    <a href="index.php" class="btn btn-primary btn-connect">
        <i class="bi bi-box-arrow-in-right"></i>
        Cliquer ici
    </a>

</div>

</body>
</html>