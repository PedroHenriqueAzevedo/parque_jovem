<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../../login.php');
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
        header('Location: listar.php?updated=1');
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
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Editar Arquivo da Escola Sabatina</h1>
    <a href="listar.php" class="btn btn-secondary mb-3">Voltar</a>

    <?php if (isset($erro)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form action="editar.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="titulo" class="form-label">Título:</label>
            <input type="text" name="titulo" id="titulo" class="form-control" value="<?= htmlspecialchars($arquivo['titulo']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="arquivo" class="form-label">Arquivo Atual:</label>
            <div>
                <a href="../../<?= htmlspecialchars($arquivo['arquivo']) ?>" target="_blank">Visualizar Arquivo</a>
            </div>
        </div>
        <div class="mb-3">
            <label for="arquivo" class="form-label">Substituir Arquivo (opcional):</label>
            <input type="file" name="arquivo" id="arquivo" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary w-100">Salvar Alterações</button>
    </form>
</div>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
