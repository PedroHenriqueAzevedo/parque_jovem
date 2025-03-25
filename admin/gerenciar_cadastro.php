<?php 
include '../cabecalho/header.php'; 
include '../conexao/conexao.php';

// Filtros
$filtro_nome = $_GET['nome'] ?? '';
$filtro_tipo = $_GET['tipo_cadastro'] ?? '';

// Consulta com filtros
$query = "SELECT * FROM cadastros_jovens WHERE 1";
$params = [];

if (!empty($filtro_nome)) {
    $query .= " AND nome LIKE ?";
    $params[] = "%$filtro_nome%";
}
if (!empty($filtro_tipo)) {
    $query .= " AND tipo_cadastro = ?";
    $params[] = $filtro_tipo;
}

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


            <!-- Filtros -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-6">
                    <input type="text" name="nome" class="form-control" placeholder="Filtrar por nome" value="<?= htmlspecialchars($filtro_nome) ?>">
                </div>
                <div class="col-md-4">
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
            </form>

            <!-- Tabela com aviso para mobile -->
            <div class="table-responsive">
                <!-- Aviso para mobile -->
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
