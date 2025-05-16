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

    if (!empty($filtro_id)) {
        $query .= " AND id = ?";
        $params[] = $filtro_id;
    }
    if (!empty($filtro_nome)) {
        $query .= " AND nome LIKE ?";
        $params[] = "%$filtro_nome%";
    }
    if (!empty($filtro_tipo)) {
        $query .= " AND tipo_cadastro = ?";
        $params[] = $filtro_tipo;
    }

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

    $pdf->SetTitle('Relatório de Cadastros de Jovens');
    $pdf->SetAuthor('Parque Jovem');
    $pdf->SetCreator('FPDF');
    $pdf->SetSubject('Lista de Cadastros');
    $pdf->SetKeywords('cadastro, jovens, relatório, igreja, PDF');

    // Cabeçalho
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

            $nome = iconv('UTF-8', 'windows-1252', $c['nome']);
            $telefone = iconv('UTF-8', 'windows-1252', $c['telefone']);
            $tipoCadastro = iconv('UTF-8', 'windows-1252', $c['tipo_cadastro']);
            $adventista = iconv('UTF-8', 'windows-1252', $c['adventista']);
            $igreja = iconv('UTF-8', 'windows-1252', ($c['igreja'] !== null && trim($c['igreja']) !== '') ? $c['igreja'] : '-');

            $linhasNome = ceil($pdf->GetStringWidth($nome) / ($larguras[1] - 2));
            $linhasTipo = ceil($pdf->GetStringWidth($tipoCadastro) / ($larguras[3] - 2));
            $linhasIgreja = ceil($pdf->GetStringWidth($igreja) / ($larguras[5] - 2));
            $linhas = max(1, $linhasNome, $linhasTipo, $linhasIgreja);

            $alturaTotal = $linhas * $alturaLinha;

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

                $repetir = true;
                continue;
            }

            $x = $pdf->GetX();
            $y = $pdf->GetY();

            $pdf->SetXY($x, $y);
            $pdf->Cell($larguras[0], $alturaTotal, $c['id'], 1, 0, 'C', $fill);

            $pdf->SetXY($x + $larguras[0], $y);
            if ($linhasNome > 1) {
                $pdf->MultiCell($larguras[1], $alturaLinha, $nome, 1, 'L', $fill);
                $pdf->SetXY($x + $larguras[0] + $larguras[1], $y);
            } else {
                $pdf->Cell($larguras[1], $alturaTotal, $nome, 1, 0, 'L', $fill);
            }

            $pdf->Cell($larguras[2], $alturaTotal, $telefone, 1, 0, 'L', $fill);

            if ($linhasTipo > 1) {
                $pdf->MultiCell($larguras[3], $alturaLinha, $tipoCadastro, 1, 'L', $fill);
                $pdf->SetXY($x + $larguras[0] + $larguras[1] + $larguras[2] + $larguras[3], $y);
            } else {
                $pdf->Cell($larguras[3], $alturaTotal, $tipoCadastro, 1, 0, 'L', $fill);
            }

            $pdf->Cell($larguras[4], $alturaTotal, $adventista, 1, 0, 'C', $fill);

            $pdf->SetXY($x + array_sum($larguras) - $larguras[5], $y);
            if ($linhasIgreja > 1) {
                $pdf->MultiCell($larguras[5], $alturaLinha, $igreja, 1, 'L', $fill);
            } else {
                $pdf->Cell($larguras[5], $alturaTotal, $igreja, 1, 1, 'L', $fill);
            }

            $pdf->SetY($y + $alturaTotal);
            $fill = !$fill;
        }
    }

    // 🔥 Agora FORÇANDO o download direto:
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="cadastros.pdf"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    $pdf->Output('F', 'php://output'); // Gera direto no output
    exit;
}
?>

