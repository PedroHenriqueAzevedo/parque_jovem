<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../../login.php');
    exit;
}

// Caminho correto para buscar.php
require_once __DIR__ . '/../controllers/banner/buscar.php';


if (!file_exists($caminho)) {
    die("O arquivo não existe no caminho: $caminho");
}

require_once $caminho;

// Chamar a função para buscar os banners
$banners = buscarBanners();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Banners - Parque Joven</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="../index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <a href="adicionar_banner.php" class="btn btn-primary">
            <i class="bi bi-plus"></i> Adicionar Banner
        </a>
    </div>

    <h1 class="text-center">Gerenciar Banners</h1>

    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>#</th>
                <th>Título</th>
                <th>Imagem</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($banners as $banner): ?>
            <tr>
                <td><?= $banner['id'] ?></td>
                <td><?= htmlspecialchars($banner['titulo']) ?></td>
                <td>
                    <img src="<?= htmlspecialchars($banner['imagem']) ?>" alt="<?= htmlspecialchars($banner['titulo']) ?>" width="100">
                </td>
                <td>
                    <a href="editar_banner.php?id=<?= $banner['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                    <a href="../controllers/banner/excluir.php?id=<?= $banner['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente excluir este banner?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
