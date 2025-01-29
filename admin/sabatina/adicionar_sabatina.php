<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../../login.php');
    exit;
}
require_once __DIR__ . '/../controllers/sabatina/adicionar.php';

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = adicionarArquivoEscolaSabatina($_POST, $_FILES);
    if ($resultado['sucesso']) {
        $_SESSION['mensagem_sucesso'] = 'Arquivo adicionado com sucesso!';
        header('Location: listar.php');
        exit;
    } else {
        $erro = $resultado['erro'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Arquivo - Escola Sabatina</title>
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
<?php include '../../cabecalho/header.php'; ?>
<body>
<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="listar.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <h1 class="text-center">Adicionar Arquivo da Escola Sabatina</h1>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger">
                <strong>Erro:</strong> <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form action="adicionar_sabatina.php" method="POST" enctype="multipart/form-data" id="formAdicionar">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título:</label>
                <input type="text" name="titulo" id="titulo" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="arquivo" class="form-label">Selecionar Arquivo:</label>
                <input type="file" name="arquivo" id="arquivo" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100" id="btnSubmit">
                <span id="spinner" class="spinner-border spinner-border-sm me-2" style="display: none;" role="status" aria-hidden="true"></span>
                Salvar
            </button>
        </form>
    </div>
</div>
<?php include '../../cabecalho/footer_ad.php'; ?>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('formAdicionar').addEventListener('submit', function () {
        const btnSubmit = document.getElementById('btnSubmit');
        const spinner = document.getElementById('spinner');
        
        // Exibir o spinner e desativar o botão
        spinner.style.display = 'inline-block';
        btnSubmit.disabled = true;
    });
</script>
</body>
</html>
