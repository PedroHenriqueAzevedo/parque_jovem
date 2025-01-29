<?php
include(__DIR__ . '/../conexao/conexao.php'); // Conexão com o banco de dados

try {
    // Consulta para buscar os projetos
    $stmt = $conexao->prepare("SELECT * FROM projetos ORDER BY id DESC");
    $stmt->execute();
    $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar imagens associadas a cada projeto
    foreach ($projetos as $key => $projeto) {
        $stmtFotos = $conexao->prepare("SELECT caminho FROM projetos_fotos WHERE projeto_id = :projeto_id");
        $stmtFotos->bindParam(':projeto_id', $projeto['id'], PDO::PARAM_INT);
        $stmtFotos->execute();
        $projetos[$key]['fotos'] = $stmtFotos->fetchAll(PDO::FETCH_COLUMN);
    }
} catch (PDOException $e) {
    die("Erro ao buscar projetos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projetos - Parque Jovem</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        .descricao-container {
            max-height: 5.6em;
            overflow: hidden;
            position: relative;
            transition: max-height 0.3s ease;
        }
        .ver-mais-menos {
            cursor: pointer;
            color: blue;
            font-weight: bold;
            display: none;
        }
        .card-img-top, .carousel-inner img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <?php if (!empty($projetos)): ?>
        <h2 class="text-center mb-4">Projetos Parque Jovem</h2>
        <div class="row">
            <?php foreach ($projetos as $projeto): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div id="carousel-<?= $projeto['id'] ?>" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php if (!empty($projeto['fotos'])): ?>
                                    <?php foreach ($projeto['fotos'] as $index => $foto): ?>
                                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                            <img src="<?= htmlspecialchars($foto) ?>" alt="Imagem do Projeto <?= $index + 1 ?>" class="d-block w-100">
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="carousel-item active">
                                        <img src="../assets/img/sem-imagem.jpg" alt="Sem imagem disponível" class="d-block w-100">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?= $projeto['id'] ?>" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?= $projeto['id'] ?>" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Próximo</span>
                            </button>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"> <?= htmlspecialchars($projeto['nome']) ?> </h5>
                            <p class="card-text descricao-container" id="descricao-<?= $projeto['id'] ?>">
                                <?= nl2br(htmlspecialchars($projeto['conteudo'])) ?>
                            </p>
                            <span class="ver-mais-menos" id="toggle-<?= $projeto['id'] ?>" onclick="toggleDescricao(<?= $projeto['id'] ?>)">Ver mais</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center text-muted">Nenhum projeto encontrado.</p>
    <?php endif; ?>
</div>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleDescricao(id) {
        const descricao = document.getElementById(`descricao-${id}`);
        const toggle = document.getElementById(`toggle-${id}`);
        
        if (descricao.style.maxHeight === "none") {
            descricao.style.maxHeight = "5.6em";
            toggle.textContent = "Ver mais";
        } else {
            descricao.style.maxHeight = "none";
            toggle.textContent = "Ver menos";
        }
    }
    
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.descricao-container').forEach(container => {
            const toggleButton = container.nextElementSibling;
            if (container.scrollHeight > container.offsetHeight && toggleButton) {
                toggleButton.style.display = "inline";
            }
        });
    });
</script>
</body>
</html>
