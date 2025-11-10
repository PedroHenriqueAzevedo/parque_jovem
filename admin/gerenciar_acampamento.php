<?php
// ========================= GERAR CSV =========================
if (isset($_POST['gerar_csv'])) {
    include '../conexao/conexao.php';

    $filtro_id = $_POST['id'] ?? '';
    $filtro_nome = $_POST['nome'] ?? '';

    $query = "SELECT * FROM inscricoes_acampamento WHERE 1";
    $params = [];

    if (!empty($filtro_id)) {
        $query .= " AND id = ?";
        $params[] = $filtro_id;
    }
    if (!empty($filtro_nome)) {
        $query .= " AND nome LIKE ?";
        $params[] = "%$filtro_nome%";
    }

    $query .= " ORDER BY id ASC";
    $stmt = $conexao->prepare($query);
    $stmt->execute($params);
    $cadastros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cabeçalhos do arquivo CSV
    header('Content-Type: text/csv; charset=ISO-8859-1');
    header('Content-Disposition: attachment; filename="inscricoes_acampamento.csv"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    $output = fopen('php://output', 'w');

    // Cabeçalho da planilha (sem acento em "Numero")
    fputcsv($output, [
        'ID', 'Nome', 'CPF', 'Data de Nascimento', 'Telefone', 'Igreja',
        'CEP', 'Rua', 'Numero', 'Bairro', 'Cidade', 'UF', 'Data Cadastro'
    ], ';');

    // Linhas
    foreach ($cadastros as $c) {
        fputcsv($output, [
            $c['id'],
            utf8_decode($c['nome']),
            utf8_decode($c['cpf']),
            date('d/m/Y', strtotime($c['data_nascimento'])),
            utf8_decode($c['telefone']),
            utf8_decode($c['igreja']),
            utf8_decode($c['cep']),
            utf8_decode($c['rua']),
            utf8_decode($c['numero']),
            utf8_decode($c['bairro']),
            utf8_decode($c['cidade']),
            utf8_decode($c['estado']),
            date('d/m/Y', strtotime($c['data_cadastro']))
        ], ';');
    }

    fclose($output);
    exit;
}
?>

<style>
body {
    background-image: url('../assets/images/image.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
}
</style>

<?php 
include '../cabecalho/header.php'; 
include '../conexao/conexao.php';

$filtro_id = $_GET['id'] ?? '';
$filtro_nome = $_GET['nome'] ?? '';

$query = "SELECT * FROM inscricoes_acampamento WHERE 1";
$params = [];

if (!empty($filtro_id)) {
    $query .= " AND id = ?";
    $params[] = $filtro_id;
}
if (!empty($filtro_nome)) {
    $query .= " AND nome LIKE ?";
    $params[] = "%$filtro_nome%";
}
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

            <h3 class="card-title mb-4 text-center">Gerenciar Inscrições do Acampamento</h3>

            <!-- Filtros -->
            <form method="GET" class="row g-3 align-items-end mb-3">
                <div class="col-md-2">
                    <input type="number" name="id" class="form-control" placeholder="Filtrar por ID" value="<?= htmlspecialchars($filtro_id) ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="nome" class="form-control" placeholder="Filtrar por nome" value="<?= htmlspecialchars($filtro_nome) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>

                <div class="col-md-2">
                    <button type="submit" form="csvForm" class="btn btn-success w-100">
                        <i class="bi bi-file-earmark-excel"></i> Planilha
                    </button>
                </div>
            </form>

            <!-- Formulário oculto para gerar CSV -->
            <form method="POST" id="csvForm">
                <input type="hidden" name="id" value="<?= htmlspecialchars($filtro_id) ?>">
                <input type="hidden" name="nome" value="<?= htmlspecialchars($filtro_nome) ?>">
                <input type="hidden" name="gerar_csv" value="1">
            </form>

            <!-- Tabela -->
            <div class="table-responsive">
                <div class="alert alert-info text-center d-block d-md-none">
                    Arraste para o lado para ver toda a tabela →
                </div>

                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Data Nasc.</th>
                            <th>Telefone</th>
                            <th>Igreja</th>
                            <th>CEP</th>
                            <th>Rua</th>
                            <th>Número</th>
                            <th>Bairro</th>
                            <th>Cidade</th>
                            <th>UF</th>
                            <th>Data Cad.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($cadastros) > 0): ?>
                            <?php foreach ($cadastros as $c): ?>
                                <tr>
                                    <td><?= htmlspecialchars($c['id']) ?></td>
                                    <td><?= htmlspecialchars($c['nome']) ?></td>
                                    <td><?= htmlspecialchars($c['cpf']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($c['data_nascimento'])) ?></td>
                                    <td><?= htmlspecialchars($c['telefone']) ?></td>
                                    <td><?= htmlspecialchars($c['igreja']) ?></td>
                                    <td><?= htmlspecialchars($c['cep']) ?></td>
                                    <td><?= htmlspecialchars($c['rua']) ?></td>
                                    <td><?= htmlspecialchars($c['numero']) ?></td>
                                    <td><?= htmlspecialchars($c['bairro']) ?></td>
                                    <td><?= htmlspecialchars($c['cidade']) ?></td>
                                    <td><?= htmlspecialchars($c['estado']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($c['data_cadastro'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="13" class="text-center">Nenhuma inscrição encontrada.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../cabecalho/footer_ad.php'; ?>
