<?php
// Verifica se a sessão já está iniciada antes de chamar session_start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../../login.php');
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
    <title>Gerenciar Banners - Parque Joven</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>
<?php include '../../cabecalho/header.php'; ?>
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

<script>
    function confirmDelete(id) {
        if (confirm('Deseja realmente excluir este banner?')) {
            fetch(`../controllers/banner/excluir.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success alert-dismissible fade show';
                        alertDiv.role = 'alert';
                        alertDiv.innerHTML = `${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
                        document.querySelector('.container').prepend(alertDiv);

                        document.getElementById(`banner-${id}`).remove();
                    } else {
                        alert('Erro: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Ocorreu um erro ao tentar excluir o banner.');
                });
        }
    }
</script>
<?php include '../../cabecalho/footer.php'; ?>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
