<?php
// Tu peux ajouter ici du PHP plus tard si nécessaire
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>JH SOLUTION</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- PWA -->
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" sizes="192x192" href="iconjh-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="iconjh-180.png">

    <meta name="theme-color" content="#0d6efd">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <style>
        body {
            height: 100vh;
            margin: 0;
            background: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: system-ui;
        }

        .splash {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo {
            width: 220px;
            animation: pulse 2.5s infinite;
        }

        .btn-connect {
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            margin-top: 25px;
        }

        .loader {
            display: none;
            margin-top: 35px;
            width: 220px;
            height: 6px;
            background: #e9ecef;
            border-radius: 20px;
            overflow: hidden;
        }

        .loader-bar {
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, #0d6efd, #4dabf7);
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.04); }
            100% { transform: scale(1); }
        }
    </style>
</head>

<body>

<div class="splash">

    <img src="iconjh-512.png" alt="JH Solution" class="logo mb-4">

    <button id="btnConnect" class="btn btn-primary btn-connect">
        <i class="bi bi-box-arrow-in-right"></i> Cliquer ici
    </button>

    <div id="loader" class="loader mt-4">
        <div class="loader-bar"></div>
    </div>

</div>

<script>

    // ================= SERVICE WORKER =================
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {

            navigator.serviceWorker.register('service-worker.js')

                .then(reg => {
                    console.log('Service Worker enregistré:', reg);
                })

                .catch(err => {
                    console.log('Erreur SW:', err);
                });

        });
    }

    // ================= SPLASH =================
    const btn = document.getElementById("btnConnect");
    const loader = document.getElementById("loader");

    btn.addEventListener("click", () => {

        btn.style.display = "none";
        loader.style.display = "block";

        localStorage.setItem("firstLaunchDone", "true");

        setTimeout(() => {

            // Redirection vers page PHP
            window.location.href = "VERIFI_maritime.html";

        }, 3000);

    });

</script>

</body>
</html>