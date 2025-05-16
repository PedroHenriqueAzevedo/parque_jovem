<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Parque Jovem'; ?></title>

    <!-- Favicon -->
    <link rel="icon" href="/parque_jovem/assets/images/logo.png" type="image/png">

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        .brand-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .header-logo {
            max-width: 80px;
            height: auto;
        }
        .bg-parque-jovem {
            background-color: #2D8C28; /* Cor verde correta */
        }
    </style>
</head>
<body class="d-flex flex-column" style="min-height: 100vh;">
<header class="bg-parque-jovem text-white py-2">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <a href="index.php">
                <img src="/parque_jovem/assets/images/logo.png" alt="Logo Parque Jovem" class="header-logo">
            </a>
            <h1 class="brand-title text-center flex-grow-1 m-0">Parque Jovem</h1>
        </div>
    </div>
</header>
</body>
</html>
