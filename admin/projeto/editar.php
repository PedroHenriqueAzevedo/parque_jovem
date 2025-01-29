<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../../login.php');
    exit;
}

require_once __DIR__ . '/../controllers/projeto/buscar.php';
require_once __DIR__ . '/../controllers/projeto/editar.php';

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit;
}

$id = $_GET['id'];
$projeto = buscarProjetoPorId($id);

if (!$projeto) {
    $_SESSION['mensagem_erro'] = "Projeto não encontrado!";
    header('Location: listar.php');
    exit;
}

// Pegar imagens existentes do banco
$imagensExistentes = $projeto['fotos'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../conexao/conexao.php';


    $resultado = editarProjeto($id, $_POST, $_FILES);
    if ($resultado['sucesso']) {
        $_SESSION['mensagem_sucesso'] = 'Projeto atualizado com sucesso!';
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
    <title>Editar Projeto</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <style>
        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .preview-item {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
        }

        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
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
<?php include '../../cabecalho/header.php'; ?>
<body>
<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="listar.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <h1 class="text-center">Editar Projeto</h1>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger"> <?= htmlspecialchars($erro) ?> </div>
        <?php endif; ?>

        <form action="editar.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" id="form-editar">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome do Projeto:</label>
                <input type="text" name="nome" id="nome" class="form-control" value="<?= htmlspecialchars($projeto['nome']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="conteudo" class="form-label">Descrição:</label>
                <textarea name="conteudo" id="conteudo" class="form-control" rows="4" required><?= htmlspecialchars($projeto['conteudo']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Imagens do Projeto:</label>
                <div class="preview-container" id="preview-container">
                    <?php foreach ($imagensExistentes as $imagem): ?>
                        <div class="preview-item">
                            <img src="../../<?= htmlspecialchars($imagem) ?>" alt="Imagem">
                            <button type="button" class="remove-btn" onclick="removerImagemExistente(this, '<?= $imagem ?>')">&times;</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="mb-3">
                <label for="fotos" class="form-label">Adicionar Novas Imagens:</label>
                <input type="file" name="fotos[]" id="fotos" class="form-control" accept="image/*" multiple>
                <small class="text-muted">As imagens adicionadas serão somadas às existentes.</small>
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
    document.addEventListener('DOMContentLoaded', function () {
        const fotosInput = document.getElementById('fotos');
        const previewContainer = document.getElementById('preview-container');
        let imagensSelecionadas = [];

        fotosInput.addEventListener('change', function (event) {
            const arquivos = Array.from(event.target.files);

            arquivos.forEach((arquivo) => {
                if (!imagensSelecionadas.some(img => img.name === arquivo.name)) {
                    imagensSelecionadas.push(arquivo);
                }
            });

            adicionarNovasImagens();
        });

        function adicionarNovasImagens() {
            imagensSelecionadas.forEach((arquivo) => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const previewItem = document.createElement("div");
                    previewItem.classList.add("preview-item");

                    const imgElement = document.createElement("img");
                    imgElement.src = e.target.result;

                    const removeButton = document.createElement("button");
                    removeButton.innerHTML = "&times;";
                    removeButton.classList.add("remove-btn");
                    removeButton.onclick = function () {
                        previewItem.remove();
                    };

                    previewItem.appendChild(imgElement);
                    previewItem.appendChild(removeButton);
                    previewContainer.appendChild(previewItem);
                };
                reader.readAsDataURL(arquivo);
            });

            const dataTransfer = new DataTransfer();
            imagensSelecionadas.forEach((arquivo) => dataTransfer.items.add(arquivo));
            fotosInput.files = dataTransfer.files;
        }

        window.removerImagemExistente = function (botao, caminho) {
            if (confirm("Tem certeza que deseja remover esta imagem?")) {
                botao.parentElement.remove();
            }
        };

        document.getElementById('form-editar').addEventListener('submit', function () {
            const btnSubmit = document.getElementById('btnSubmit');
            const spinner = document.getElementById('spinner');
            spinner.style.display = 'inline-block';
            btnSubmit.disabled = true;
        });
    });
</script>
</body>
</html>
