<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../../login.php');
    exit;
}

// Incluir os arquivos necessários
require_once __DIR__ . '/../controllers/sabatina/buscar_sabatina.php';

// Verificar se há uma mensagem de exclusão na sessão
$mensagem = '';
if (isset($_SESSION['mensagem_exclusao'])) {
    $mensagem = $_SESSION['mensagem_exclusao'];
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
    <title>Gerenciar Escola Sabatina - Parque Joven</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="../index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        <a href="adicionar_sabatina.php" class="btn btn-primary">
            <i class="bi bi-plus"></i> Adicionar Arquivo
        </a>
    </div>

    <h1 class="text-center">Gerenciar Escola Sabatina</h1>

    <?php if ($mensagem): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensagem) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

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

<script>
    function confirmDelete(id) {
        if (confirm('Deseja realmente excluir este arquivo?')) {
            fetch(`../controllers/sabatina/excluir_sabatina.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success alert-dismissible fade show';
                        alertDiv.role = 'alert';
                        alertDiv.innerHTML = `${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
                        document.querySelector('.container').prepend(alertDiv);

                        document.getElementById(`arquivo-${id}`).remove();
                    } else {
                        alert('Erro: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Ocorreu um erro ao tentar excluir o arquivo.');
                });
        }
    }
</script>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
