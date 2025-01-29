<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../../admin/login.php');
    exit;
}

require_once __DIR__ . '/../controllers/banner/buscar.php';
require_once __DIR__ . '/../controllers/banner/editar.php';

if (!isset($_GET['id'])) {
    header('Location: banners.php');
    exit;
}

$id = $_GET['id'];
$banner = buscarBannerPorId($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = editarBanner($id, $_POST, $_FILES);
    if ($resultado['sucesso']) {
        header('Location: banners.php?updated=1');
        exit;
    } else {
        $erro = $resultado['erro'];
    }
}
include '../../cabecalho/header.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Banner - Parque Joven</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <style>
        body {
            background-image: url('../../assets/images/image.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="banners.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <h1 class="text-center">Editar Banner</h1>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger"> <?= htmlspecialchars($erro) ?> </div>
        <?php endif; ?>

        <form action="editar_banner.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título do Banner:</label>
                <input type="text" name="titulo" id="titulo" class="form-control" value="<?= htmlspecialchars($banner['titulo']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="imagem" class="form-label">Imagem Atual:</label>
                <div>
                    <img src="../../uploads/<?= htmlspecialchars($banner['imagem']) ?>" alt="<?= htmlspecialchars($banner['titulo']) ?>" width="100">
                </div>
            </div>
            <div class="mb-3">
                <label for="imagem" class="form-label">Substituir Imagem (opcional):</label>
                <input type="file" name="imagem" id="imagem" class="form-control" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary w-100">Salvar Alterações</button>
        </form>
    </div>
</div>
<?php include '../../cabecalho/footer_ad.php';?>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>