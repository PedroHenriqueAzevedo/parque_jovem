<?php
include '../cabecalho/header.php';
include '../conexao/conexao.php';

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
    <title>Escola Sabatina</title>
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
        .pdf-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .pdf-thumbnail {
            background: rgba(0, 0, 0, 0.7);
            padding: 15px;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.2s;
            text-align: center;
        }
        .pdf-thumbnail:hover {
            transform: scale(1.03);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
        }
        .pdf-thumbnail canvas {
            border: 1px solid #ddd;
            max-width: 100%;
            background: white;
            margin-bottom: 10px;
            height: 300px;
        }
        .pdf-title {
            color: white;
            font-weight: bold;
            margin-top: 10px;
            font-size: 16px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .pdf-date {
            color: #aaa;
            font-size: 14px;
            margin-top: 5px;
        }
        .pdf-viewer {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            overflow-y: auto;
        }
        .pdf-container {
            text-align: center;
            margin-top: 20px;
            background: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            width: 100%;
            max-width: 900px;
        }
        .pdf-viewer canvas {
            border: 1px solid #ddd;
            margin-bottom: 10px;
            max-width: 100%;
            background: white;
        }
        .page-input {
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
            margin-top: 20px;
        }
        .btn-download:hover {
            background-color: #e0a800;
        }
        .btn-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            font-size: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }
        .btn-close:hover {
            background-color: #c82333;
        }
        .nav-buttons {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: nowrap;
            gap: 10px;
            margin-top: 15px;
        }
        .nav-buttons span {
            font-size: 18px;
            font-weight: bold;
        }
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1001;
        }
        .spinner-border {
            width: 5rem;
            height: 5rem;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="mt-4 mb-3">
        <a href="../index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
    <h2 class="text-center mt-4">Arquivos da Escola Sabatina</h2>
    
    <?php if ($arquivos): ?>
        <div class="pdf-grid">
            <?php foreach ($arquivos as $arquivo): ?>
                <div class="pdf-thumbnail" data-file="<?= htmlspecialchars($arquivo['arquivo']); ?>" data-title="<?= htmlspecialchars($arquivo['titulo']); ?>">
                    <canvas id="thumbnail-<?= htmlspecialchars(basename($arquivo['arquivo'], '.pdf')); ?>"></canvas>
                    <div class="pdf-title"><?= htmlspecialchars($arquivo['titulo']); ?></div>
                    <div class="pdf-date">
                        <?= date('d/m/Y', strtotime($arquivo['data_upload'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center text-white">Nenhum arquivo disponível no momento.</p>
    <?php endif; ?>
</div>

<div class="pdf-viewer">
    <button class="btn-close">&times;</button>
    <div class="pdf-container">
        <canvas id="pdf-render"></canvas>
        <div class="nav-buttons">
            <button class="btn btn-primary btn-custom prev">Anterior</button>
            <span>Página <input type="number" class="page-input" min="1"> de <span class="page-count"></span></span>
            <button class="btn btn-primary btn-custom next">Próxima</button>
        </div>
        <a href="#" class="btn btn-download" download>Baixar</a>
    </div>
    <div class="loading-overlay">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
    </div>
</div>

<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
    
    document.querySelectorAll('.pdf-thumbnail').forEach(thumbnail => {
        const file = thumbnail.dataset.file;
        const url = '../' + file;
        const canvas = thumbnail.querySelector('canvas');
        const ctx = canvas.getContext('2d');
        
        pdfjsLib.getDocument(url).promise.then(pdfDoc => {
            return pdfDoc.getPage(1);
        }).then(page => {
            const viewport = page.getViewport({ scale: 0.5 });
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            
            return page.render({
                canvasContext: ctx,
                viewport: viewport
            }).promise;
        }).catch(err => {
            console.error('Erro ao carregar miniatura:', err);
            canvas.height = 200;
            canvas.width = 150;
            ctx.fillStyle = '#f8f9fa';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.font = '14px Arial';
            ctx.fillStyle = '#dc3545';
            ctx.textAlign = 'center';
            ctx.fillText('Erro ao carregar', canvas.width/2, canvas.height/2);
        });
    });
    
    let pdfDoc = null,
        pageNum = 1,
        pageIsRendering = false,
        pageNumPending = null,
        scale = 1.5,
        currentFile = '';
    
    const pdfViewer = document.querySelector('.pdf-viewer');
    const pdfCanvas = document.getElementById('pdf-render');
    const ctx = pdfCanvas.getContext('2d');
    const pageInput = document.querySelector('.page-input');
    const pageCount = document.querySelector('.page-count');
    const prevButton = document.querySelector('.prev');
    const nextButton = document.querySelector('.next');
    const downloadButton = document.querySelector('.btn-download');
    const closeButton = document.querySelector('.btn-close');
    const loadingOverlay = document.querySelector('.loading-overlay');
    
    const renderPage = num => {
        pageIsRendering = true;
        
        pdfDoc.getPage(num).then(page => {
            const viewport = page.getViewport({ scale });
            pdfCanvas.height = viewport.height;
            pdfCanvas.width = viewport.width;
            
            const renderContext = {
                canvasContext: ctx,
                viewport
            };
            
            page.render(renderContext).promise.then(() => {
                pageIsRendering = false;
                
                if (pageNumPending !== null) {
                    renderPage(pageNumPending);
                    pageNumPending = null;
                } else {
                    loadingOverlay.style.display = 'none';
                }
            });
            
            pageInput.value = num;
        });
    };
    
    const queueRenderPage = num => {
        if (pageIsRendering) {
            pageNumPending = num;
        } else {
            renderPage(num);
        }
    };
    
    const showPrevPage = () => {
        if (pageNum <= 1) {
            return;
        }
        pageNum--;
        queueRenderPage(pageNum);
    };
    
    const showNextPage = () => {
        if (pageNum >= pdfDoc.numPages) {
            return;
        }
        pageNum++;
        queueRenderPage(pageNum);
    };
    
    const openPdfViewer = (file, title) => {
        currentFile = file;
        const url = '../' + file;
        
        loadingOverlay.style.display = 'flex';
        pdfViewer.style.display = 'flex';
        
        pageNum = 1;
        
        downloadButton.href = url;
        downloadButton.textContent = 'Baixar ' + title;
        
        pdfjsLib.getDocument(url).promise.then(pdfDoc_ => {
            pdfDoc = pdfDoc_;
            pageCount.textContent = pdfDoc.numPages;
            pageInput.max = pdfDoc.numPages;
            
            renderPage(pageNum);
        }).catch(err => {
            console.error('Erro ao carregar o PDF:', err);
            loadingOverlay.style.display = 'none';
            alert('Erro ao carregar o PDF. Por favor, tente novamente mais tarde.');
        });
    };
    
    document.querySelectorAll('.pdf-thumbnail').forEach(thumbnail => {
        thumbnail.addEventListener('click', () => {
            const file = thumbnail.dataset.file;
            const title = thumbnail.dataset.title;
            openPdfViewer(file, title);
        });
    });
    
    prevButton.addEventListener('click', showPrevPage);
    nextButton.addEventListener('click', showNextPage);
    
    pageInput.addEventListener('change', () => {
        const page = parseInt(pageInput.value);
        if (page > 0 && page <= pdfDoc.numPages) {
            pageNum = page;
            queueRenderPage(pageNum);
        } else {
            pageInput.value = pageNum;
        }
    });
    
    closeButton.addEventListener('click', () => {
        pdfViewer.style.display = 'none';
        pdfDoc = null;
    });
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && pdfViewer.style.display === 'flex') {
            pdfViewer.style.display = 'none';
            pdfDoc = null;
        }
    });
</script>

<?php include '../cabecalho/footer.php'; ?>
</body>
</html>