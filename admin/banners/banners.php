<?php
// Verifica se a sessão já está iniciada antes de chamar session_start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../../admin/login.php');
    exit;
}

// Incluir os arquivos necessários
require_once __DIR__ . '/../controllers/banner/buscar.php';

// Verificar se há mensagens de sucesso ou exclusão na sessão
$mensagem = '';
if (isset($_SESSION['mensagem_exclusao'])) {
    $mensagem = $_SESSION['mensagem_exclusao'];
    unset($_SESSION['mensagem_exclusao']);
}

if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem = $_SESSION['mensagem_sucesso'];
    unset($_SESSION['mensagem_sucesso']);
}

// Chamar a função para buscar os banners
$banners = buscarBanners();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Gerenciamento - Banners</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>
<?php include '../../cabecalho/header.php'; ?>
<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="../index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <a href="adicionar_banner.php" class="btn btn-primary">
                <i class="bi bi-plus"></i> Adicionar Banner
            </a>
        </div>

        <h1 class="text-center">Painel de Gerenciamento de Banners</h1>

        <div id="mensagem-exclusao"></div>

        <?php if ($mensagem): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($mensagem) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <table class="table table-bordered table-hover mt-3">
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
                <tr id="banner-<?= $banner['id'] ?>">
                    <td><?= htmlspecialchars($banner['id']) ?></td>
                    <td><?= htmlspecialchars($banner['titulo']) ?></td>
                    <td>
                        <img src="../../uploads/<?= htmlspecialchars($banner['imagem']) ?>" alt="<?= htmlspecialchars($banner['titulo']) ?>" width="100">
                    </td>
                    <td>
                        <a href="editar_banner.php?id=<?= $banner['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="#" class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $banner['id'] ?>)">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Tem certeza de que deseja excluir este banner?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteButton">Excluir</button>
            </div>
        </div>
    </div>
</div>

<script>
    let deleteId = null;

    function confirmDelete(id) {
        deleteId = id;
        var confirmDeleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
        confirmDeleteModal.show();
    }

    document.getElementById('confirmDeleteButton').addEventListener('click', function() {
        if (deleteId !== null) {
            fetch(`../controllers/banner/excluir.php?id=${deleteId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`banner-${deleteId}`).remove();
                        document.getElementById('mensagem-exclusao').innerHTML = '<div class="alert alert-success alert-dismissible fade show" role="alert">' + data.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    }
                });
        }
        var confirmDeleteModal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
        confirmDeleteModal.hide();
    });
</script>
<?php include '../../cabecalho/footer_ad.php'; ?>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
