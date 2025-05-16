<?php
// GERAÇÃO DO PDF – deve vir antes de qualquer saída
if (isset($_POST['gerar_pdf'])) {
    require_once __DIR__ . '/../lib/fpdf.php';
    include '../conexao/conexao.php';

    // Filtros para PDF (obtidos via POST)
    $filtro_id   = $_POST['id'] ?? '';
    $filtro_nome = $_POST['nome'] ?? '';
    $filtro_tipo = $_POST['tipo_cadastro'] ?? '';

    $query = "SELECT * FROM cadastros_jovens WHERE 1";
    $params = [];

    // Filtro por ID
    if (!empty($filtro_id)) {
        $query .= " AND id = ?";
        $params[] = $filtro_id;
    }
    // Filtro por Nome
    if (!empty($filtro_nome)) {
        $query .= " AND nome LIKE ?";
        $params[] = "%$filtro_nome%";
    }
    // Filtro por Tipo de Cadastro
    if (!empty($filtro_tipo)) {
        $query .= " AND tipo_cadastro = ?";
        $params[] = $filtro_tipo;
    }

    // Ordenação crescente por ID
    $query .= " ORDER BY id ASC";

    $stmt = $conexao->prepare($query);
    $stmt->execute($params);
    $cadastros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    class PDF extends FPDF {
        function Header() {
            $this->SetFillColor(30, 144, 255);
            $this->SetTextColor(255);
            $this->SetFont('Arial', 'B', 14);
            $this->Cell(0, 12, iconv('UTF-8', 'windows-1252', 'Relatório de Cadastros de Jovens'), 0, 1, 'C', true);
            $this->Ln(5);
        }
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Página ') . $this->PageNo(), 0, 0, 'C');
        }
    }

    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 10);

    // Cabeçalho do PDF
    $pdf->SetFillColor(100, 149, 237);
    $pdf->SetTextColor(255);
    $pdf->Cell(10, 10, 'ID', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Nome', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Telefone', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Tipo', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'Advent.', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Igreja', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 10);
    $fill = false;

   foreach ($cadastros as $c) {
    $repetir = true;
    while ($repetir) {
        $repetir = false;

        $pdf->SetFillColor($fill ? 245 : 220);
        $pdf->SetTextColor(0);

        $larguras = [10, 40, 30, 50, 20, 40];
        $alturaLinha = 8;

        // Prepara conteúdo
        $nome = iconv('UTF-8', 'windows-1252', $c['nome']);
        $telefone = iconv('UTF-8', 'windows-1252', $c['telefone']);
        $tipoCadastro = iconv('UTF-8', 'windows-1252', $c['tipo_cadastro']);
        $adventista = iconv('UTF-8', 'windows-1252', $c['adventista']);
        $igreja = iconv('UTF-8', 'windows-1252', ($c['igreja'] !== null && trim($c['igreja']) !== '') ? $c['igreja'] : '-');

        // Calcula número de linhas
        $linhasNome = ceil($pdf->GetStringWidth($nome) / ($larguras[1] - 2));
        $linhasTipo = ceil($pdf->GetStringWidth($tipoCadastro) / ($larguras[3] - 2));
        $linhasIgreja = ceil($pdf->GetStringWidth($igreja) / ($larguras[5] - 2));
        $linhas = max(1, $linhasNome, $linhasTipo, $linhasIgreja);

        $alturaTotal = $linhas * $alturaLinha;

        // Se ultrapassar limite da página
        if ($pdf->GetY() + $alturaTotal > 270) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(100, 149, 237);
            $pdf->SetTextColor(255);
            $pdf->Cell(10, 10, 'ID', 1, 0, 'C', true);
            $pdf->Cell(40, 10, 'Nome', 1, 0, 'C', true);
            $pdf->Cell(30, 10, 'Telefone', 1, 0, 'C', true);
            $pdf->Cell(50, 10, 'Tipo', 1, 0, 'C', true);
            $pdf->Cell(20, 10, 'Advent.', 1, 0, 'C', true);
            $pdf->Cell(40, 10, 'Igreja', 1, 1, 'C', true);
            $pdf->SetFont('Arial', '', 10);

            $repetir = true; // Redesenhar o mesmo cadastro
            continue;
        }

        // Posição inicial
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        // ID
        $pdf->SetXY($x, $y);
        $pdf->Cell($larguras[0], $alturaTotal, $c['id'], 1, 0, 'C', $fill);

        // Nome
        $pdf->SetXY($x + $larguras[0], $y);
        if ($linhasNome > 1) {
            $pdf->MultiCell($larguras[1], $alturaLinha, $nome, 1, 'L', $fill);
            $pdf->SetXY($x + $larguras[0] + $larguras[1], $y); // Reposiciona
        } else {
            $pdf->Cell($larguras[1], $alturaTotal, $nome, 1, 0, 'L', $fill);
        }

        // Telefone
        $pdf->Cell($larguras[2], $alturaTotal, $telefone, 1, 0, 'L', $fill);

        // Tipo de Cadastro
        if ($linhasTipo > 1) {
            $pdf->MultiCell($larguras[3], $alturaLinha, $tipoCadastro, 1, 'L', $fill);
            $pdf->SetXY($x + $larguras[0] + $larguras[1] + $larguras[2] + $larguras[3], $y);
        } else {
            $pdf->Cell($larguras[3], $alturaTotal, $tipoCadastro, 1, 0, 'L', $fill);
        }

        // Adventista
        $pdf->Cell($larguras[4], $alturaTotal, $adventista, 1, 0, 'C', $fill);

        // Igreja
        $pdf->SetXY($x + array_sum($larguras) - $larguras[5], $y);
        if ($linhasIgreja > 1) {
            $pdf->MultiCell($larguras[5], $alturaLinha, $igreja, 1, 'L', $fill);
        } else {
            $pdf->Cell($larguras[5], $alturaTotal, $igreja, 1, 1, 'L', $fill);
        }

        // Atualiza Y para a próxima linha
        $pdf->SetY($y + $alturaTotal);
        $fill = !$fill;
    }
}

    
    
    $pdf->Output();
    exit;
}
?>
  <style>
        body {
            background-image: url('../assets/images/image.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
        }
    
    </style>
<?php 
include '../cabecalho/header.php'; 
include '../conexao/conexao.php';

// Filtros para exibição na tela (obtidos via GET)
$filtro_id   = $_GET['id'] ?? '';
$filtro_nome = $_GET['nome'] ?? '';
$filtro_tipo = $_GET['tipo_cadastro'] ?? '';

$query = "SELECT * FROM cadastros_jovens WHERE 1";
$params = []; // <-- Adiciona isso aqui


// Filtro por ID
if ($filtro_id !== '') {
    $query .= " AND id = ?";
    $params[] = $filtro_id;
}


// Filtro por Nome
if (!empty($filtro_nome)) {
    $query .= " AND nome LIKE ?";
    $params[] = "%$filtro_nome%";
}

// Filtro por Tipo
if (!empty($filtro_tipo)) {
    $query .= " AND tipo_cadastro = ?";
    $params[] = $filtro_tipo;
}

// Ordenação crescente por ID
$query .= " ORDER BY id ASC";

$stmt = $conexao->prepare($query);
$stmt->execute($params);
$cadastros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5 mb-5">
    <div class="card shadow">
        <div class="card-body">
            <a href="../index.php" class="btn btn-secondary mb-2">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <h3 class="card-title mb-4 text-center">Gerenciar Cadastros de Jovens</h3>

<!-- Filtros + Botão PDF juntos na mesma linha -->
<form method="GET" class="row g-3 align-items-end mb-3">
    <div class="col-md-2">
        <input type="number" name="id" class="form-control" placeholder="Filtrar por ID" value="<?= htmlspecialchars($filtro_id) ?>">
    </div>
    <div class="col-md-3">
        <input type="text" name="nome" class="form-control" placeholder="Filtrar por nome" value="<?= htmlspecialchars($filtro_nome) ?>">
    </div>
    <div class="col-md-3">
        <select name="tipo_cadastro" class="form-select">
            <option value="">Todos os tipos</option>
            <option value="Tenho interesse em me batizar" <?= $filtro_tipo == 'Tenho interesse em me batizar' ? 'selected' : '' ?>>Tenho interesse em me batizar</option>
            <option value="Quero oração" <?= $filtro_tipo == 'Quero oração' ? 'selected' : '' ?>>Quero oração</option>
            <option value="Quero participar da classe biblíca" <?= $filtro_tipo == 'Quero participar da classe biblíca' ? 'selected' : '' ?>>Quero participar da classe bíblica</option>
        </select>
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
    </div>
    <!-- Botão PDF com mesma altura e largura -->
    <div class="col-md-2">
        <button type="submit" form="pdfForm" class="btn btn-danger w-100">
            <i class="bi bi-file-earmark-pdf"></i> Gerar PDF
        </button>
    </div>
</form>

<!-- Formulário oculto para o PDF -->
<form method="POST" id="pdfForm">
    <input type="hidden" name="id" value="<?= htmlspecialchars($filtro_id) ?>">
    <input type="hidden" name="nome" value="<?= htmlspecialchars($filtro_nome) ?>">
    <input type="hidden" name="tipo_cadastro" value="<?= htmlspecialchars($filtro_tipo) ?>">
    <input type="hidden" name="gerar_pdf" value="1">
</form>


            <!-- Tabela -->
            <div class="table-responsive">
                <div class="alert alert-info text-center d-block d-md-none">
                    Arraste para o lado para ver toda a tabela →
                </div>

                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Telefone</th>
                            <th>Tipo de Cadastro</th>
                            <th>É Adventista?</th>
                            <th>Igreja</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($cadastros) > 0): ?>
                            <?php foreach ($cadastros as $cadastro): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cadastro['id']) ?></td>
                                    <td><?= htmlspecialchars($cadastro['nome']) ?></td>
                                    <td class="text-nowrap"><?= htmlspecialchars($cadastro['telefone']) ?></td>
                                    <td><?= htmlspecialchars($cadastro['tipo_cadastro']) ?></td>
                                    <td><?= htmlspecialchars($cadastro['adventista']) ?></td>
                                    <td><?= htmlspecialchars($cadastro['igreja'] ?? '-') ?></td>
                                    <td><?= date('d/m/Y', strtotime($cadastro['data_cadastro'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Nenhum cadastro encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../cabecalho/footer_ad.php'; ?>
