<?php
include '../conexao/conexao.php';
include '../cabecalho/header_acampamento.php';

// Fuso horário
date_default_timezone_set('America/Sao_Paulo');

// ======================================================
// PUXAR VAGAS DA TABELA "acomodacoes"
// ======================================================
$acomodacoesRaw = $conexao->query("SELECT * FROM acomodacoes")->fetchAll(PDO::FETCH_ASSOC);
$acomodacoes = [];

// corrigir contagem REAL somente pelos responsáveis
foreach ($acomodacoesRaw as $a) {

    // preço não importa — buscamos por prefixo
    $prefixo = $a['nome'];

    $stmt = $conexao->prepare("
        SELECT COUNT(*) FROM inscricoes_acampamento
        WHERE responsavel_id IS NULL
        AND acomodacao LIKE CONCAT(?, '%')
    ");
    $stmt->execute([$prefixo]);

    $usadoReal = $stmt->fetchColumn();

    $a['usado'] = $usadoReal; // sobrescreve com valor correto

    $acomodacoes[$a['nome']] = $a;
}

function vagasRestantes($ac) {
    return max(0, $ac['limite'] - $ac['usado']);
}

// Criar mapa para facilitar o uso
$map = [];
foreach ($acomodacoes as $a) {
    $map[$a['nome']] = $a; 
}

// MAPEAMENTO PARA SALVAR COM PREÇO (OPÇÃO B)
$precos = [
    'Suíte 4 leitos'        => 'Suíte 4 leitos - R$ 2.000,00',
    'Suíte 3 leitos'        => 'Suíte 3 leitos - R$ 1.500,00',
    'Alojamento Coletivo'   => 'Alojamento coletivo - R$ 250,00 por pessoa',
    'Barraca'               => 'Barraca - R$ 250,00',
    'Day Use'               => 'Day Use - R$ 200,00', // NOVO
];

$mensagem = '';
$sucesso = false;

// ======================================================
// PROCESSAR FORMULÁRIO
// ======================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $data_nascimento = $_POST['data_nascimento'];
    $telefone = trim($_POST['telefone']);
    $igreja = $_POST['igreja'];
    $acomodacao_raw = $_POST['acomodacao'] ?? '';
    $forma_pagamento = $_POST['forma_pagamento'] ?? '';

    // Transformar na versão "nome + preço"
    $acomodacao = $precos[$acomodacao_raw] ?? '';

    // Arrays acompanhantes
    $acomp_nomes = $_POST['acomp_nome'] ?? [];
    $acomp_cpfs = $_POST['acomp_cpf'] ?? [];
    $acomp_datas = $_POST['acomp_data_nascimento'] ?? [];
    $acomp_tels = $_POST['acomp_telefone'] ?? [];

    // Quantidade obrigatória
    $qtd_acomp_obrig = 0;

    if ($acomodacao_raw === 'Suíte 3 leitos') $qtd_acomp_obrig = 2;
    if ($acomodacao_raw === 'Suíte 4 leitos') $qtd_acomp_obrig = 3;

    // Day Use NÃO TEM ACOMPANHANTES
    if ($acomodacao_raw === 'Day Use') $qtd_acomp_obrig = 0;

    // ----------------- VALIDAÇÃO BÁSICA ------------------
    if (
        empty($nome) || empty($cpf) || empty($data_nascimento) ||
        empty($telefone) || empty($igreja) || empty($acomodacao) ||
        empty($forma_pagamento)
    ) {
        $mensagem = "<div class='alert alert-danger text-center'>Preencha todos os campos obrigatórios.</div>";
    } else {

        // ---------- VALIDAR VAGAS PARA TUDO EXCETO DAY USE ----------
        if ($acomodacao_raw !== 'Day Use') {
            if (vagasRestantes($map[$acomodacao_raw]) <= 0) {
                $mensagem = "<div class='alert alert-danger text-center'>As vagas para esta acomodação acabaram!</div>";
            }
        }

        if ($mensagem === '') {

            // ---------- Validar acompanhantes ----------
            if ($qtd_acomp_obrig > 0) {
                for ($i = 0; $i < $qtd_acomp_obrig; $i++) {
                    if (
                        empty($acomp_nomes[$i]) ||
                        empty($acomp_cpfs[$i]) ||
                        empty($acomp_datas[$i]) ||
                        empty($acomp_tels[$i])
                    ) {
                        $mensagem = "<div class='alert alert-danger text-center'>
                            Preencha todos os dados dos acompanhantes.
                        </div>";
                        break;
                    }
                }
            }
        }

        if ($mensagem === '') {

            // ---------- Validar duplicidade banco ----------
            $todosNomes = array_merge([$nome], array_slice($acomp_nomes, 0, $qtd_acomp_obrig));
            $todosCpfs = array_merge([$cpf], array_slice($acomp_cpfs, 0, $qtd_acomp_obrig));
            $todosTels = array_merge([$telefone], array_slice($acomp_tels, 0, $qtd_acomp_obrig));

            $phN = implode(',', array_fill(0, count($todosNomes), '?'));
            $phC = implode(',', array_fill(0, count($todosCpfs), '?'));
            $phT = implode(',', array_fill(0, count($todosTels), '?'));

            $sqlDup = "
                SELECT id FROM inscricoes_acampamento
                WHERE nome IN ($phN)
                OR cpf IN ($phC)
                OR telefone IN ($phT)
                LIMIT 1
            ";

            $stmtDup = $conexao->prepare($sqlDup);
            $stmtDup->execute(array_merge($todosNomes, $todosCpfs, $todosTels));

            if ($stmtDup->fetch()) {
                $mensagem = "<div class='alert alert-danger text-center'>Já existe uma inscrição com estes dados.</div>";
            } else {

                // ==========================================================
                // SALVAR NO BANCO
                // ==========================================================
                try {
                    $conexao->beginTransaction();
                    $data_cadastro = date('Y-m-d H:i:s');

                    // Salvar responsável
                    $stmt = $conexao->prepare("
                        INSERT INTO inscricoes_acampamento
                        (responsavel_id, nome, cpf, data_nascimento, telefone, igreja, acomodacao, forma_pagamento, data_cadastro)
                        VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$nome, $cpf, $data_nascimento, $telefone, $igreja, $acomodacao, $forma_pagamento, $data_cadastro]);

                    $idResp = $conexao->lastInsertId();

                    // Salvar acompanhantes
                    if ($qtd_acomp_obrig > 0) {
                        $stmtA = $conexao->prepare("
                            INSERT INTO inscricoes_acampamento
                            (responsavel_id, nome, cpf, data_nascimento, telefone, igreja, acomodacao, forma_pagamento, data_cadastro)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        for ($i = 0; $i < $qtd_acomp_obrig; $i++) {
                            $stmtA->execute([
                                $idResp,
                                $acomp_nomes[$i],
                                $acomp_cpfs[$i],
                                $acomp_datas[$i],
                                $acomp_tels[$i],
                                $igreja,
                                $acomodacao,
                                $forma_pagamento,
                                $data_cadastro
                            ]);
                        }
                    }

                    // Atualizar contagem (NÃO ATUALIZA se for Day Use)
                    if ($acomodacao_raw !== 'Day Use') {
                        $conexao->prepare("UPDATE acomodacoes SET usado = usado + 1 WHERE nome = ?")
                                ->execute([$acomodacao_raw]);
                    }

                    $conexao->commit();

                    $sucesso = true;
                    $mensagem = "<div class='alert alert-success text-center fw-bold'>Inscrição realizada com sucesso!</div>";

                } catch (Exception $e) {
                    $conexao->rollBack();
                    $mensagem = "<div class='alert alert-danger text-center'>Erro ao salvar.</div>";
                }
            }
        }
    }
}
?>

<!-- Bootstrap (caso falhe no header) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background-image: url('../assets/images/image.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
}
</style>

<div class="container mt-5 mb-5">
    <div class="card shadow">
        <div class="card-body">

            <a href="../index.php" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>

            <h3 class="card-title mb-4 text-center fw-bold">Inscrição para o Acampamento</h3>

            <?= $mensagem ?>

<?php if (!$sucesso): ?>

<form method="POST">

    <!-- DADOS RESPONSÁVEL -->
    <div class="mb-3">
        <label class="form-label fw-semibold">Nome completo:</label>
        <input type="text" name="nome" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">CPF:</label>
        <input type="text" name="cpf" id="cpf" class="form-control" required maxlength="14">
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">Data de nascimento:</label>
        <input type="date" name="data_nascimento" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">Telefone:</label>
        <input type="text" name="telefone" id="telefone" class="form-control" required maxlength="15">
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">Igreja:</label>
        <select name="igreja" class="form-select" required>
            <option value="">Selecione</option>
            <option value="IASD Parque Flamboyant">IASD Parque Flamboyant</option>
            <option value="IASD Setor Sul">IASD Setor Sul</option>
            <option value="IASD Lago Azul">IASD Lago Azul</option>
            <option value="IASD Central">IASD Central</option>
            <option value="IASD Jardim Pompeia">IASD Jardim Pompeia</option>
            <option value="IASD Vila Nova">IASD Vila Nova</option>
            <option value="IASD Setor Pedro Ludovico">IASD Setor Pedro Ludovico</option>
            <option value="IASD Parque Amazônia">IASD Parque Amazônia</option>
            <option value="IASD Coimbra">IASD Coimbra</option>
            <option value="IASD Palmito">IASD Palmito</option>
            <option value="IASD Universitário">IASD Universitário</option>
            <option value="IASD Bueno">IASD Bueno</option>
            <option value="IASD Vila Brasília">IASD Vila Brasília</option>
            <option value="Outra">Outra</option>
        </select>
    </div>

    <!-- ACOMODAÇÕES -->
    <hr>
    <h5 class="fw-bold text-center">Escolha sua Acomodação</h5>

    <div class="alert alert-info text-center">⚠️ Apenas 1 opção permitida.</div>

    <h6 class="fw-bold mt-3">Apartamentos</h6>

    <?php if (vagasRestantes($map['Suíte 4 leitos']) > 0): ?>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="acomodacao"
               value="Suíte 4 leitos" required>
        <label class="form-check-label">
            Suíte com 4 leitos — R$ 2.000,00
            (<?= vagasRestantes($map['Suíte 4 leitos']) ?> vagas restantes)
        </label>
    </div>
    <?php endif; ?>

    <?php if (vagasRestantes($map['Suíte 3 leitos']) > 0): ?>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="acomodacao"
               value="Suíte 3 leitos">
        <label class="form-check-label">
            Suíte com 3 leitos — R$ 1.500,00
            (<?= vagasRestantes($map['Suíte 3 leitos']) ?> vagas restantes)
        </label>
    </div>
    <?php endif; ?>

    <h6 class="fw-bold mt-3">Alojamento Coletivo</h6>

    <?php if (vagasRestantes($map['Alojamento Coletivo']) > 0): ?>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="acomodacao"
               value="Alojamento Coletivo">
        <label class="form-check-label">
            Alojamento coletivo — R$ 250,00 por pessoa
            (<?= vagasRestantes($map['Alojamento Coletivo']) ?> vagas seguintes)
        </label>
    </div>
    <?php endif; ?>

    <h6 class="fw-bold mt-3">Barracas</h6>
    <div class="form-check mb-4">
        <input class="form-check-input" type="radio" name="acomodacao" value="Barraca">
        <label class="form-check-label">
            Espaço para barraca — R$ 250,00 (ilimitado)
        </label>
    </div>

    <h6 class="fw-bold mt-3">Day Use</h6>
    <div class="form-check mb-4">
        <input class="form-check-input" type="radio" name="acomodacao" value="Day Use">
        <label class="form-check-label">
            Day Use — R$ 200,00  
            <br><small class="text-muted">Para pessoas que NÃO vão dormir no local, apenas permanecer durante o dia.</small>
        </label>
    </div>

    <!-- ACOMPANHANTES -->
    <div id="acompanhantes-container" style="display:none;">
        <h5 class="fw-bold mt-4">Pessoas que ficarão com você</h5>

        <?php for ($i = 1; $i <= 3; $i++): ?>
        <div class="border rounded p-3 mb-3 bloco-acompanhante">
            <h6 class="fw-bold">Pessoa <?= $i+1 ?></h6>
            <input type="text" name="acomp_nome[]" class="form-control mb-2" placeholder="Nome completo">
            <input type="text" name="acomp_cpf[]" class="form-control mb-2 acomp_cpf" placeholder="CPF">
            <input type="date" name="acomp_data_nascimento[]" class="form-control mb-2">
            
            <!-- TELEFONE DO ACOMPANHANTE (com máscara) -->
            <input type="text" name="acomp_telefone[]" class="form-control acomp_tel mb-2" placeholder="Telefone" maxlength="15">
        </div>
        <?php endfor; ?>
    </div>

    <!-- PAGAMENTO -->
    <hr>
    <h5 class="fw-bold mt-3">Forma de Pagamento</h5>

    <div class="form-check">
        <input class="form-check-input" type="radio" name="forma_pagamento" value="PIX" required>
        <label class="form-check-label">PIX</label>
    </div>

    <div class="form-check mb-4">
        <input class="form-check-input" type="radio" name="forma_pagamento" value="Cartão de Crédito">
        <label class="form-check-label">Cartão de Crédito</label>
    </div>

    <button type="submit" class="btn btn-primary w-100 mt-3">
        Enviar inscrição
    </button>

</form>

<?php endif; ?>

        </div>
    </div>
</div>

<script>
// Máscara CPF do responsável
document.getElementById('cpf').addEventListener('input', e => {
    let v = e.target.value.replace(/\D/g, '');
    v = v.replace(/(\d{3})(\d)/, '$1.$2')
         .replace(/(\d{3})(\d)/, '$1.$2')
         .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    e.target.value = v.substring(0, 14);
});

// Máscara telefone do responsável
document.getElementById('telefone').addEventListener('input', e => {
    let v = e.target.value.replace(/\D/g, '');
    if (v.length <= 10)
        v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
    else
        v = v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
    e.target.value = v;
});

// Máscara CPF dos acompanhantes
document.querySelectorAll('.acomp_cpf').forEach(input => {
    input.addEventListener('input', e => {
        let v = e.target.value.replace(/\D/g, '');
        v = v.replace(/(\d{3})(\d)/, '$1.$2')
             .replace(/(\d{3})(\d)/, '$1.$2')
             .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        e.target.value = v.substring(0, 14);
    });
});

// Máscara telefone acompanhantes
document.querySelectorAll('.acomp_tel').forEach(input => {
    input.addEventListener('input', e => {
        let v = e.target.value.replace(/\D/g, '');
        if (v.length <= 10)
            v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        else
            v = v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
        e.target.value = v.substring(0, 15);
    });
});

// Lógica acompanhantes
function atualizarAcompanhantes() {
    let escolha = document.querySelector('input[name="acomodacao"]:checked');
    let qtd = 0;

    if (!escolha) return;

    if (escolha.value === 'Suíte 3 leitos') qtd = 2;
    if (escolha.value === 'Suíte 4 leitos') qtd = 3;

    // Day Use NÃO PODE TER acompanhantes
    if (escolha.value === 'Day Use') qtd = 0;

    const container = document.getElementById('acompanhantes-container');
    const blocos = document.querySelectorAll('.bloco-acompanhante');

    container.style.display = qtd > 0 ? 'block' : 'none';

    blocos.forEach((b, i) => {
        if (i < qtd) {
            b.style.display = 'block';
            b.querySelectorAll('input').forEach(inp => inp.required = true);
        } else {
            b.style.display = 'none';
            b.querySelectorAll('input').forEach(inp => {
                inp.required = false;
                inp.value = '';
            });
        }
    });
}

document.querySelectorAll('input[name="acomodacao"]').forEach(r => {
    r.addEventListener('change', atualizarAcompanhantes);
});
window.addEventListener('load', atualizarAcompanhantes);
</script>

<?php include '../cabecalho/footer_acampamento.php'; ?>
