<?php
// Conexão com o banco de dados
include './conexao/conexao.php';

// Verifica se a conexão foi estabelecida corretamente
if (!$conexao) {
    die("Erro na conexão com o banco de dados.");
}

// Consulta para obter os banners
try {
    $stmt = $conexao->prepare("SELECT * FROM banners");
    $stmt->execute();
    $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar banners: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrossel de Banners</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <style>
        /* Estilo específico para esse carrossel */
        #bannerCarousel .carousel-inner {
            position: relative;
        }

        #bannerCarousel .carousel-item img {
            width: 100%; /* Garante que a imagem ocupe toda a largura */
            height: 400px; /* Define uma altura fixa */
            object-fit: cover; /* Mantém a proporção e corta o excesso */
            display: block; /* Evita espaçamentos indesejados */
        }

        /* Evita que imagens de outros carrosséis sejam afetadas */
        .outro-carousel .carousel-item img {
            height: 300px; /* Altura diferente para outro carrossel */
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div id="bannerCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($banners as $index => $banner): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="./uploads/<?php echo $banner['imagem']; ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($banner['titulo']); ?>">
                        <div class="carousel-caption d-none d-md-block">
                            <h5><?php echo htmlspecialchars($banner['titulo']); ?></h5>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

