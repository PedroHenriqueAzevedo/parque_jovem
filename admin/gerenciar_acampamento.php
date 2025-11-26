<?php
// ========================= UPDATE STATUS PAGAMENTO =========================
if (isset($_POST['atualizar_status'])) {

    include '../conexao/conexao.php';

    $id = $_POST['id'] ?? 0;
    $status = $_POST['status'] ?? '';

    if ($id > 0 && $status !== '') {

        // Atualizar responsável
        $stmt = $conexao->prepare("
            UPDATE inscricoes_acampamento 
            SET status_pagamento = ? 
            WHERE id = ?
        ");
        $stmt->execute([$status, $id]);

        // Atualizar acompanhantes automaticamente
        $stmt2 = $conexao->prepare("
            UPDATE inscricoes_acampamento
            SET status_pagamento = ?
            WHERE responsavel_id = ?
        ");
        $stmt2->execute([$status, $id]);
    }

    exit;
}

// ========================= GERAR CSV =========================
if (isset($_POST['gerar_csv'])) {
    include '../conexao/conexao.php';

    if (ob_get_level()) ob_end_clean();

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
    $cadastros = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    header('Content-Type: text/csv; charset=ISO-8859-1');
    header('Content-Disposition: attachment; filename="inscricoes_acampamento.csv"');

    $output = fopen('php://output', 'w');

    fputcsv($output, [
        'ID', 'Nome', 'CPF', 'Data de Nascimento', 'Telefone',
        'Igreja', 'Acomodação', 'Forma Pagamento', 'Status Pagamento', 'Responsável', 'Data Cadastro'
    ], ';');

    foreach ($cadastros as $c) {

        if (!empty($c['responsavel_id'])) {
            $stmt_resp = $conexao->prepare("SELECT nome FROM inscricoes_acampamento WHERE id = ?");
            $stmt_resp->execute([$c['responsavel_id']]);
            $responsavel_nome = $stmt_resp->fetchColumn();
        } else {
            $responsavel_nome = "É o responsável";
        }

        fputcsv($output, [
            $c['id'],
            utf8_decode($c['nome']),
            utf8_decode($c['cpf']),
            date('d/m/Y', strtotime($c['data_nascimento'])),
            utf8_decode($c['telefone']),
            utf8_decode($c['igreja']),
            utf8_decode($c['acomodacao']),
            utf8_decode($c['forma_pagamento']),
            utf8_decode($c['status_pagamento']),
            utf8_decode($responsavel_nome),
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

.card {
    min-height: 170px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.card-body p {
    margin: 0;
    padding: 0;
}
</style>

<?php 
include '../cabecalho/header.php'; 
include '../conexao/conexao.php';

// ========================= CONTADORES =========================
$total_suite4   = 16;
$total_suite3   = 3;
$total_coletivo = 73;

// COLETA REAL DO BANCO
$cont_suite4 = $conexao->query("
    SELECT COUNT(*) FROM inscricoes_acampamento 
    WHERE responsavel_id IS NULL 
    AND acomodacao='Suíte 4 leitos - R$ 2.000,00'
")->fetchColumn();

$cont_suite3 = $conexao->query("
    SELECT COUNT(*) FROM inscricoes_acampamento 
    WHERE responsavel_id IS NULL 
    AND acomodacao='Suíte 3 leitos - R$ 1.500,00'
")->fetchColumn();

$cont_coletivo = $conexao->query("
    SELECT COUNT(*) FROM inscricoes_acampamento 
    WHERE acomodacao='Alojamento coletivo - R$ 250,00 por pessoa'
")->fetchColumn();

$cont_barraca = $conexao->query("
    SELECT COUNT(*) FROM inscricoes_acampamento 
    WHERE acomodacao='Barraca - R$ 250,00'
")->fetchColumn();

$cont_dayuse = $conexao->query("
    SELECT COUNT(*) FROM inscricoes_acampamento 
    WHERE acomodacao='Day Use - R$ 200,00'
")->fetchColumn();

// RESTANTES
$rest_suite4 = max(0, $total_suite4 - $cont_suite4);
$rest_suite3 = max(0, $total_suite3 - $cont_suite3);
$rest_coletivo = max(0, $total_coletivo - $cont_coletivo);

// ========================= CONSULTA TABELA =========================
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

$cadastros = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
?>

<div class="container mt-5 mb-5">
    <div class="card shadow">
        <div class="card-body">

            <a href="../index.php" class="btn btn-secondary mb-2">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>

            <h3 class="card-title mb-4 text-center fw-bold">Gerenciar Inscrições do Acampamento</h3>

            <!-- ========================= CARDS AJUSTADOS ========================= -->
            <div class="row text-center mb-4">

                <div class="col-6 col-md-2 mb-3">
                    <div class="card border-primary shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Suíte 4 Leitos</h5>
                            <p><b><?= $cont_suite4 ?></b> inscritos</p>
                            <small>Vagas restantes: <?= $rest_suite4 ?></small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-2 mb-3">
                    <div class="card border-success shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Suíte 3 Leitos</h5>
                            <p><b><?= $cont_suite3 ?></b> inscritos</p>
                            <small>Vagas restantes: <?= $rest_suite3 ?></small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-2 mb-3">
                    <div class="card border-warning shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Alojamento Coletivo</h5>
                            <p><b><?= $cont_coletivo ?></b> inscritos</p>
                            <small>Vagas restantes: <?= $rest_coletivo ?></small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-2 mb-3">
                    <div class="card border-danger shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Barracas</h5>
                            <p><b><?= $cont_barraca ?></b> inscritos</p>
                            <small>Sem limite</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-2 mb-3">
                    <div class="card border-info shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Day Use</h5>
                            <p><b><?= $cont_dayuse ?></b> inscritos</p>
                            <small>Sem limite</small>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Filtros -->
            <form method="GET" class="row g-3 align-items-end mb-3">
                <div class="col-md-2">
                    <input type="number" name="id" class="form-control" placeholder="Filtrar por ID"
                           value="<?= htmlspecialchars($filtro_id) ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="nome" class="form-control" placeholder="Filtrar por nome"
                           value="<?= htmlspecialchars($filtro_nome) ?>">
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

            <form method="POST" id="csvForm">
                <input type="hidden" name="id" value="<?= htmlspecialchars($filtro_id) ?>">
                <input type="hidden" name="nome" value="<?= htmlspecialchars($filtro_nome) ?>">
                <input type="hidden" name="gerar_csv" value="1">
            </form>

            <!-- Tabela -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Data Nasc.</th>
                            <th>Telefone</th>
                            <th>Igreja</th>
                            <th>Acomodação</th>
                            <th>Forma Pagamento</th>
                            <th>Status Pagamento</th>
                            <th>Responsável</th>
                            <th>Data Cad.</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (count($cadastros) > 0): ?>
                        <?php foreach ($cadastros as $c): ?>

                            <?php
                            if (!empty($c['responsavel_id'])) {
                                $stmt_resp = $conexao->prepare("SELECT nome FROM inscricoes_acampamento WHERE id = ?");
                                $stmt_resp->execute([$c['responsavel_id']]);
                                $resp_nome = $stmt_resp->fetchColumn();
                            } else {
                                $resp_nome = "É o responsável";
                            }
                            ?>

                            <tr>
                                <td><?= $c['id'] ?></td>
                                <td><?= htmlspecialchars($c['nome']) ?></td>
                                <td><?= htmlspecialchars($c['cpf']) ?></td>
                                <td><?= date('d/m/Y', strtotime($c['data_nascimento'])) ?></td>
                                <td><?= htmlspecialchars($c['telefone']) ?></td>
                                <td><?= $c['responsavel_id'] ? "—" : htmlspecialchars($c['igreja']) ?></td>
                                <td><?= htmlspecialchars($c['acomodacao']) ?></td>
                                <td><?= htmlspecialchars($c['forma_pagamento']) ?></td>

                                <td>
                                    <select class="form-select status-pagamento"
                                            data-id="<?= $c['id'] ?>"
                                            data-resp="<?= $c['responsavel_id'] ?: $c['id'] ?>"
                                            <?= $c['responsavel_id'] ? 'disabled' : '' ?>>
                                        <option value="Pendente" <?= $c['status_pagamento']=='Pendente' ? 'selected' : '' ?>>Pendente</option>
                                        <option value="Pago - PIX" <?= $c['status_pagamento']=='Pago - PIX' ? 'selected' : '' ?>>Pago - PIX</option>
                                        <option value="Pago - Cartão" <?= $c['status_pagamento']=='Pago - Cartão' ? 'selected' : '' ?>>Pago - Cartão</option>
                                    </select>
                                </td>

                                <td><?= htmlspecialchars($resp_nome) ?></td>
                                <td><?= date('d/m/Y', strtotime($c['data_cadastro'])) ?></td>
                            </tr>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center">Nenhuma inscrição encontrada.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>

                </table>
            </div>

        </div>
    </div>
</div>

<script>
document.querySelectorAll('.status-pagamento').forEach(select => {
    select.addEventListener('change', function () {

        let id = this.dataset.id;
        let status = this.value;

        let formData = new FormData();
        formData.append("atualizar_status", "1");
        formData.append("id", id);
        formData.append("status", status);

        fetch("", {
            method: "POST",
            body: formData
        })
        .then(r => r.text())
        .then(() => {
            document.querySelectorAll(`.status-pagamento[data-resp="${id}"]`).forEach(sel => {
                sel.value = status;
            });
        });

    });
});
</script>

<?php include '../cabecalho/footer_ad.php'; ?>
