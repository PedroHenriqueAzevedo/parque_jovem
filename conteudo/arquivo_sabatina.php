<?php
include './conexao/conexao.php';

// Buscar arquivos da Escola Sabatina
try {
    $query = "SELECT titulo, arquivo, data_upload FROM escola_sabatina ORDER BY data_upload DESC";
    $stmt = $conexao->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar os dados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arquivos da Escola Sabatina</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Arquivos da Escola Sabatina</h2>
    <div class="list-group">
        <?php foreach ($result as $row): ?>
            <a href="<?= dirname($_SERVER['PHP_SELF']) . './uploads/' . htmlspecialchars($row['arquivo']) ?>" 
   download 
   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
    <div>
        <h5 class="mb-1"><?= htmlspecialchars($row['titulo']); ?></h5>
        <small>Data de Upload: <?= date('d/m/Y', strtotime($row['data_upload'])); ?></small>
    </div>
    <span class="badge badge-primary badge-pill">Baixar</span>
</a>

        <?php endforeach; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
