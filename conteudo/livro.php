<?php include '../cabecalho/header.php'; ?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autêntico - Devocional Jovem 2025</title>
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
        #pdf-container {
            text-align: center;
            margin-top: 20px;
            background: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
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
    <!-- BOTÃO VOLTAR AQUI -->
    <div class="mt-4 mb-3">
        <a href="../index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
    <h2 class="text-center mt-4">Autêntico - Devocional Jovem 2025</h2>
    
    <div id="pdf-container">
        <canvas id="pdf-render"></canvas>
        <div class="mt-3 nav-buttons">
            <button id="prev" class="btn btn-primary btn-custom">Anterior</button>
            <span>Página <input type="number" id="page-input" min="1"> de <span id="page-count"></span></span>
            <button id="next" class="btn btn-primary btn-custom">Próxima</button>
        </div>
        <div class="mt-4">
            <a href="../assets/pdf/Autêntico-DevocionalJovem2025.pdf" download class="btn btn-download">Baixar Autêntico - Devocional Jovem 2025</a>
        </div>
    </div>
</div>

<script>
    const url = '../assets/pdf/Autêntico-DevocionalJovem2025.pdf';
    let pdfDoc = null,
        pageNum = 1,
        pageIsRendering = false,
        pageNumPending = null;

    const scale = 1.5;
    const canvas = document.getElementById('pdf-render');
    const ctx = canvas.getContext('2d');
    const pageInput = document.getElementById('page-input');

    const renderPage = num => {
        pageIsRendering = true;

        pdfDoc.getPage(num).then(page => {
            const viewport = page.getViewport({ scale });
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };

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

    const queueRenderPage = num => {
        if (pageIsRendering) {
            pageNumPending = num;
        } else {
            renderPage(num);
        }
    };

    document.getElementById('prev').addEventListener('click', () => {
        if (pageNum <= 1) return;
        pageNum--;
        queueRenderPage(pageNum);
    });

    document.getElementById('next').addEventListener('click', () => {
        if (pageNum >= pdfDoc.numPages) return;
        pageNum++;
        queueRenderPage(pageNum);
    });

    pageInput.addEventListener('change', () => {
        let pageNumber = parseInt(pageInput.value);
        if (pageNumber >= 1 && pageNumber <= pdfDoc.numPages) {
            pageNum = pageNumber;
            queueRenderPage(pageNum);
        } else {
            pageInput.value = pageNum;
        }
    });

    pdfjsLib.getDocument(url).promise.then(pdfDoc_ => {
        pdfDoc = pdfDoc_;
        document.getElementById('page-count').textContent = pdfDoc.numPages;
        pageInput.setAttribute("max", pdfDoc.numPages);
        renderPage(pageNum);
    }).catch(err => {
        console.error('Erro ao carregar o PDF:', err);
    });
</script>
<?php include '../cabecalho/footer.php'; ?>
</body>
</html>
