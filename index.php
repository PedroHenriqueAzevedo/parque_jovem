<?php include './cabecalho/header.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Inicial - Parque Jovem</title>
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>
<?php include './conteudo/banners.php'; ?>

<div class="container text-center mt-4">
    <a href="https://pesquisa.biblia.com.br/pt-BR/AA/" class="btn btn-primary btn-lg" target="_blank">
        <i class="bi bi-book"></i> Acesse a Bíblia Online
    </a>
</div>

<?php include './conteudo/arquivo_sabatina.php'; ?>
<?php include './cabecalho/footer.php'; ?>
<script src="./assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
