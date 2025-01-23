<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Parque Jovem'; ?></title> <!-- Título dinâmico -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body class="d-flex flex-column" style="min-height: 100vh;">
<header class="bg-dark text-white py-3">
    <div class="container-fluid px-0"> <!-- remove padding lateral -->
        <div class="d-flex justify-content-between align-items-center">
            <a href="index.php" class="d-flex align-items-center mx-3">
                <img src="/parque_jovem/assets/images/logo.png" alt="Logo Parque Jovem" width="150" height="auto">
            </a>
            <nav>
                <!-- Adicione links de navegação aqui -->
            </nav>
        </div>
    </div>
</header>
</body>
</html>
