<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../../admin/login.php');
    exit;
}

require_once __DIR__ . '/../controllers/sabatina/buscar_sabatina.php';
require_once __DIR__ . '/../controllers/sabatina/editar_sabatina.php';

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit;
}

$id = $_GET['id'];
$arquivo = buscarArquivoSabatinaPorId($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = editarArquivoSabatina($id, $_POST, $_FILES);
    if ($resultado['sucesso']) {
        $_SESSION['mensagem_sucesso'] = 'Arquivo atualizado com sucesso!';
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
    <title>Editar Arquivo - Escola Sabatina</title>
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
<?php include '../../cabecalho/header.php'; ?>
<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="listar.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <h1 class="text-center">Editar Arquivo da Escola Sabatina</h1>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger"> <?= htmlspecialchars($erro) ?> </div>
        <?php endif; ?>

        <form action="editar.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" id="form-editar">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título:</label>
                <input type="text" name="titulo" id="titulo" class="form-control" value="<?= htmlspecialchars($arquivo['titulo']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="arquivo" class="form-label">Arquivo Atual:</label>
                <div class="list-group">
                    <a href="../../<?= htmlspecialchars($arquivo['arquivo']) ?>" target="_blank" 
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span><strong>Clique aqui para visualizar o arquivo:</strong> <?= basename($arquivo['arquivo']) ?></span>
                        <i class="bi bi-eye"></i>
                    </a>
                </div>
            </div>
            <div class="mb-3">
                <label for="arquivo" class="form-label">Substituir Arquivo (opcional):</label>
                <input type="file" name="arquivo" id="arquivo" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center" id="btn-submit">
                <span id="spinner" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display: none;"></span>
                <span id="btn-text">Salvar Alterações</span>
            </button>
        </form>
    </div>
</div>
<?php include '../../cabecalho/footer_ad.php'; ?>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('form-editar').addEventListener('submit', function() {
        const submitButton = document.getElementById('btn-submit');
        const spinner = document.getElementById('spinner');
        const btnText = document.getElementById('btn-text');

        submitButton.disabled = true;
        spinner.style.display = 'inline-block';
        btnText.textContent = 'Salvando...'; // Atualiza o texto do botão enquanto carrega
    });
</script>
</body>
</html>
