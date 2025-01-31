<?php
include '../cabecalho/header.php';
include '../conexao/conexao.php';

// Buscar todos os arquivos da Escola Sabatina
try {
    $query = "SELECT titulo, arquivo, data_upload FROM escola_sabatina ORDER BY data_upload DESC";
    $stmt = $conexao->prepare($query);
    $stmt->execute();
    $arquivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar os dados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lição Da Escola Sabatina</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <style>
        body {
            background-image: url('../assets/images/image.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            color: white;
        }
        .pdf-container {
            text-align: center;
            margin-top: 20px;
            background: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        canvas {
            border: 1px solid #ddd;
            margin-bottom: 10px;
            max-width: 100%;
            background: white;
        }
        #page-input {
            width: 60px;
            text-align: center;
            border: none;
            border-radius: 5px;
            padding: 5px;
            font-weight: bold;
        }
        .btn-custom {
            font-size: 16px;
            padding: 8px 15px;
            font-weight: bold;
        }
        .btn-download {
            background-color: #ffc107;
            border: none;
            color: black;
            font-size: 18px;
            padding: 12px 25px;
            font-weight: bold;
            border-radius: 10px;
            display: block;
            width: 100%;
            text-align: center;
        }
        .btn-download:hover {
            background-color: #e0a800;
        }
        .nav-buttons {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: nowrap;
            gap: 10px;
        }
        .nav-buttons span {
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mt-4">Lição da Escola Sabatina</h2>
    
    <?php if ($arquivos): ?>
        <?php foreach ($arquivos as $arquivo): ?>
            <div class="pdf-container">
                <canvas id="pdf-render-<?= htmlspecialchars($arquivo['arquivo']); ?>"></canvas>
                <div class="mt-3 nav-buttons">
                    <button class="btn btn-primary btn-custom prev" data-file="<?= htmlspecialchars($arquivo['arquivo']); ?>">Anterior</button>
                    <span>Página <input type="number" class="page-input" data-file="<?= htmlspecialchars($arquivo['arquivo']); ?>" min="1"> de <span class="page-count" data-file="<?= htmlspecialchars($arquivo['arquivo']); ?>"></span></span>
                    <button class="btn btn-primary btn-custom next" data-file="<?= htmlspecialchars($arquivo['arquivo']); ?>">Próxima</button>
                </div>
                <div class="mt-4">
                    <a href="../<?= htmlspecialchars($arquivo['arquivo']); ?>" download class="btn btn-download">Baixar <?= htmlspecialchars($arquivo['titulo']); ?></a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center text-white">Nenhum arquivo disponível no momento.</p>
    <?php endif; ?>
</div>

<script>
    document.querySelectorAll('.pdf-container').forEach(container => {
        const file = container.querySelector('canvas').id.replace('pdf-render-', '');
        const url = '../' + file;
        let pdfDoc = null,
            pageNum = 1,
            pageIsRendering = false,
            pageNumPending = null;

        const canvas = container.querySelector('canvas');
        const ctx = canvas.getContext('2d');
        const pageInput = container.querySelector('.page-input');
        const pageCount = container.querySelector('.page-count');

        const renderPage = num => {
            pageIsRendering = true;
            pdfDoc.getPage(num).then(page => {
                const viewport = page.getViewport({ scale: 1.5 });
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                const renderContext = { canvasContext: ctx, viewport: viewport };
                page.render(renderContext).promise.then(() => {
                    pageIsRendering = false;
                    if (pageNumPending !== null) {
                        renderPage(pageNumPending);
                        pageNumPending = null;
                    }
                });
                pageInput.value = num;
            });
        };

        pdfjsLib.getDocument(url).promise.then(pdfDoc_ => {
            pdfDoc = pdfDoc_;
            pageCount.textContent = pdfDoc.numPages;
            pageInput.setAttribute("max", pdfDoc.numPages);
            renderPage(pageNum);
        }).catch(err => console.error('Erro ao carregar o PDF:', err));

        container.querySelector('.prev').addEventListener('click', () => {
            if (pageNum <= 1) return;
            pageNum--;
            renderPage(pageNum);
        });

        container.querySelector('.next').addEventListener('click', () => {
            if (pageNum >= pdfDoc.numPages) return;
            pageNum++;
            renderPage(pageNum);
        });
    });
</script>

<?php include '../cabecalho/footer.php'; ?>
</body>
</html>