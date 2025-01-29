<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../../admin/login.php');
    exit;
}

// Incluir os arquivos necessários
require_once __DIR__ . '/../controllers/sabatina/buscar_sabatina.php';

// Verificar se há uma mensagem de sucesso na sessão
$mensagem = '';
if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem = $_SESSION['mensagem_sucesso'];
    unset($_SESSION['mensagem_sucesso']);
}

// Verificar se há uma mensagem de exclusão na sessão
$mensagem_exclusao = '';
if (isset($_SESSION['mensagem_exclusao'])) {
    $mensagem_exclusao = $_SESSION['mensagem_exclusao'];
    unset($_SESSION['mensagem_exclusao']);
}

// Chamar a função para buscar os arquivos da escola sabatina
$arquivos = buscarArquivosSabatina();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Escola Sabatina</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        .table-responsive {
            overflow-x: auto;
        }
        .modal-content {
            max-width: 100%;
        }
      
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
<?php include '../../cabecalho/header.php'; ?>
<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <a href="../index.php" class="btn btn-secondary mb-2">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <a href="adicionar_sabatina.php" class="btn btn-primary mb-2">
                <i class="bi bi-plus"></i> Adicionar Arquivo
            </a>
        </div>

        <h1 class="text-center">Gerenciar Escola Sabatina</h1>

        <!-- Mensagem de Sucesso -->
        <div id="mensagem-exclusao"></div>

        <?php if ($mensagem): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($mensagem) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Título</th>
                        <th>Arquivo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arquivos as $arquivo): ?>
                    <tr id="arquivo-<?= $arquivo['id'] ?>">
                        <td><?= htmlspecialchars($arquivo['id']) ?></td>
                        <td><?= htmlspecialchars($arquivo['titulo']) ?></td>
                        <td>
                            <a href="../../<?= htmlspecialchars($arquivo['arquivo']) ?>" target="_blank" class="btn btn-info btn-sm">Visualizar</a>
                        </td>
                        <td>
                            <a href="editar.php?id=<?= $arquivo['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="#" class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $arquivo['id'] ?>)">Excluir</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                Tem certeza de que deseja excluir este arquivo?
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
            fetch(`../controllers/sabatina/excluir.php?id=${deleteId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`arquivo-${deleteId}`).remove();
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
