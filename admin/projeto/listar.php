<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../../admin/login.php');
    exit;
}

// Incluir os arquivos necessários
require_once __DIR__ . '/../controllers/projeto/buscar.php';

// Verificar se há mensagens de sucesso ou exclusão na sessão
$mensagem = $_SESSION['mensagem_sucesso'] ?? '';
unset($_SESSION['mensagem_sucesso']);

$mensagem_exclusao = $_SESSION['mensagem_exclusao'] ?? '';
unset($_SESSION['mensagem_exclusao']);

// Chamar a função para buscar os projetos
$projetos = buscarProjetos();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Projetos</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        .table-responsive {
            overflow-x: auto;
        }

        .modal-content {
            max-width: 100%;
        }

        .carousel {
            position: relative;
            max-width: 150px; /* Define um tamanho fixo para evitar desalinhamento */
            margin: auto;
        }

        .carousel-item img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 30px;
            height: 30px;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            top: 50%;
            transform: translateY(-50%);
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            width: 15px;
            height: 15px;
        }
        .conteudo-resumo {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
    max-height: 6em;
}
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
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <a href="../index.php" class="btn btn-secondary mb-2">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <a href="adicionar.php" class="btn btn-primary mb-2">
                <i class="bi bi-plus"></i> Adicionar Projeto
            </a>
        </div>

        <h1 class="text-center">Gerenciar Projetos</h1>

        <!-- Mensagens de sucesso e exclusão -->
        <div id="mensagem-container">
            <?php if ($mensagem): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($mensagem) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($mensagem_exclusao): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($mensagem_exclusao) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>Conteúdo</th>
                        <th>Imagens</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="projetos-lista">
                    <?php foreach ($projetos as $projeto): ?>
                        <tr id="projeto-<?= $projeto['id'] ?>">
                            <td><?= htmlspecialchars($projeto['id']) ?></td>
                            <td><?= htmlspecialchars($projeto['nome']) ?></td>
                            <td>
    <div class="conteudo-resumo" id="conteudo-<?= $projeto['id'] ?>">
        <?= nl2br(htmlspecialchars($projeto['conteudo'])) ?>
    </div>
    <?php if (strlen($projeto['conteudo']) > 300): ?> 
        <button class="btn btn-link p-0" onclick="toggleVerMais(<?= $projeto['id'] ?>)">Ver mais</button>
    <?php endif; ?>
</td>



                            <td>
                                <div id="carousel-<?= $projeto['id'] ?>" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        <?php 
                                        $stmt = $conexao->prepare("SELECT caminho FROM projetos_fotos WHERE projeto_id = :projeto_id");
                                        $stmt->bindParam(':projeto_id', $projeto['id'], PDO::PARAM_INT);
                                        $stmt->execute();
                                        $imagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($imagens as $index => $imagem): 
                                        ?>
                                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                                <img src="../../<?= htmlspecialchars($imagem['caminho']) ?>" alt="Imagem do Projeto">
                                            </div>
                                        <?php endforeach; ?>
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
                            </td>
                            <td>
                                <a href="editar.php?id=<?= $projeto['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="#" class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $projeto['id'] ?>)">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                Tem certeza de que deseja excluir este projeto?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteButton">Excluir</button>
            </div>
        </div>
    </div>
</div>

<script>
    let deleteId = null;

    function confirmDelete(id) {
        deleteId = id;
        const confirmDeleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
        confirmDeleteModal.show();
    }

    document.getElementById('confirmDeleteButton').addEventListener('click', function() {
        if (deleteId !== null) {
            fetch(`../controllers/projeto/excluir.php?id=${deleteId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`projeto-${deleteId}`).remove();
                        document.getElementById('mensagem-container').innerHTML = `
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                ${data.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                    }
                });
        }
        bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal')).hide();
    });

    function toggleVerMais(id) {
        let conteudo = document.getElementById('conteudo-' + id);
        let botao = conteudo.nextElementSibling;
        conteudo.classList.toggle('conteudo-resumo');
        botao.innerText = conteudo.classList.contains('conteudo-resumo') ? 'Ver mais' : 'Ver menos';
    }
</script>

<?php include '../../cabecalho/footer_ad.php'; ?>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
