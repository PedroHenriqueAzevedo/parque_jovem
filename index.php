<?php include './cabecalho/header.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Inicial - Parque Jovem</title>
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body {
            background-image: url('./assets/images/image.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
        }
    </style>
</head>
<body>

<div class="container text-center mt-4">
    <div class="d-flex justify-content-center gap-2 flex-wrap">
        <a href="https://pesquisa.biblia.com.br/pt-BR/AA/" class="btn btn-primary btn-lg" target="_blank">
            <i class="bi bi-book"></i> Acesse a Bíblia Online
        </a>
        <a href="https://chat.whatsapp.com/HzdQKz9R2jZ1OSWUM2Mwfx" class="btn btn-success btn-lg" target="_blank">
            <i class="bi bi-whatsapp"></i> Entre no Grupo do Parque Jovem
        </a>
        <a href="./conteudo/livro.php" class="btn btn-warning btn-lg">
            <i class="bi bi-file-earmark-pdf"></i> Autêntico - Devocional Jovem 2025
        </a>
        <a href="./conteudo/arquivo_sabatina.php" class="btn btn-info btn-lg">
            <i class="bi bi-journal-bookmark"></i> Lição Escola Sabatina
        </a>
        <!-- Novo botão para Cadastro de Jovens -->
        <a href="./conteudo/cadastro.php" class="btn btn-secondary btn-lg">
            <i class="bi bi-person-plus"></i> Quero me cadastrar
        </a>
    </div>
</div>

<?php include './conteudo/projeto.php'; ?>
<?php include './cabecalho/footer.php'; ?>
<script src="./assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
