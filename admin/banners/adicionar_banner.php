<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../../login.php');
    exit;
}
require_once __DIR__ . '/../controllers/banner/adicionar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = adicionarBanner($_POST, $_FILES);
    if ($resultado['sucesso']) {
        header('Location: banners.php?success=1');
        exit;
    } else {
        $erro = $resultado['erro'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<?php include '../../cabecalho/header.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Banner - Parque Joven</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="banners.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <h1 class="text-center">Adicionar Banner</h1>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger"> <?= htmlspecialchars($erro) ?> </div>
        <?php endif; ?>

        <form action="adicionar_banner.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título do Banner:</label>
                <input type="text" name="titulo" id="titulo" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="imagem" class="form-label">Imagem do Banner:</label>
                <input type="file" name="imagem" id="imagem" class="form-control" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Salvar</button>
        </form>
    </div>
</div>
<?php include '../../cabecalho/footer.php'; ?>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
