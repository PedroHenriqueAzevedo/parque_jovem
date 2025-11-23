<?php
include '../conexao/conexao.php';
include '../cabecalho/header_acampamento.php';

// Garante o fuso horário de São Paulo
date_default_timezone_set('America/Sao_Paulo');

$mensagem = '';
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $data_nascimento = $_POST['data_nascimento'];
    $telefone = trim($_POST['telefone']);
    $igreja = $_POST['igreja'];
    $acomodacao = $_POST['acomodacao'] ?? '';

    if (
        empty($nome) || empty($cpf) || empty($data_nascimento) || empty($telefone) ||
        empty($igreja) || empty($acomodacao)
    ) {
        $mensagem = "<div class='alert alert-danger text-center'>Preencha todos os campos obrigatórios.</div>";
    } else {

        $stmt = $conexao->prepare("SELECT id FROM inscricoes_acampamento WHERE nome = ? OR cpf = ? OR telefone = ?");
        $stmt->execute([$nome, $cpf, $telefone]);

        if ($stmt->fetch()) {
            $mensagem = "<div class='alert alert-danger text-center'>Já existe uma inscrição com esse nome, CPF ou telefone.</div>";
        } else {

            $data_cadastro = date('Y-m-d H:i:s');

            $stmt = $conexao->prepare("
                INSERT INTO inscricoes_acampamento 
                (nome, cpf, data_nascimento, telefone, igreja, acomodacao, data_cadastro)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            if ($stmt->execute([$nome, $cpf, $data_nascimento, $telefone, $igreja, $acomodacao, $data_cadastro])) {

                // NÃO REDIRECIONA MAIS — só mostra mensagem
                $sucesso = true;
                $mensagem = "
                    <div class='alert alert-success text-center fw-bold'>
                        Inscrição realizada com sucesso!<br>
                        A organização do acampamento entrará em contato para pagamento.
                    </div>
                ";

            } else {
                $mensagem = "<div class='alert alert-danger text-center'>Erro ao salvar a inscrição. Tente novamente.</div>";
            }
        }
    }
}
?>

<!-- Caso o header falhe, garante Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background-image: url('../assets/images/image.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    background-repeat: no-repeat;
}
</style>

<div class="container mt-5 mb-5">
    <div class="card shadow">
        <div class="card-body">

            <!-- Voltar para o site INTERNAMENTE sem apontamento externo -->
            <a href="../index.php" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>

            <h3 class="card-title mb-4 text-center fw-bold">Inscrição para o Acampamento</h3>

            <?= $mensagem ?>

            <?php if (!$sucesso): ?>   <!-- SE DER SUCESSO → SOME O FORMULÁRIO -->

            <form method="POST">

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
                        <option value="">Selecione uma igreja</option>
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

                <hr>
                <h5 class="text-center fw-bold">Escolha sua Acomodação</h5>

                <div class="alert alert-info text-center">
                    ⚠️ Só é permitido escolher <b>uma</b> opção.
                </div>

                <!-- Apartamentos -->
                <h6 class="fw-bold mt-3">Apartamentos (Ar Condicionado):</h6>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="acomodacao"
                           value="Suíte 4 leitos - R$ 2.000,00" required>
                    <label class="form-check-label">Suíte com 4 leitos — R$ 2.000,00</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="acomodacao"
                           value="Suíte 3 leitos - R$ 1.500,00">
                    <label class="form-check-label">Suíte com 3 leitos — R$ 1.500,00</label>
                </div>

                <!-- Alojamentos AC -->
                <h6 class="fw-bold mt-3">Alojamentos Coletivos (Ar Condicionado):</h6>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="acomodacao"
                           value="Alojamento 30 leitos AC - R$ 250,00">
                    <label class="form-check-label">Quarto coletivo com 30 leitos — R$ 250,00 por pessoa</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="acomodacao"
                           value="Alojamento 16 leitos AC - R$ 250,00">
                    <label class="form-check-label">Quarto coletivo com 16 leitos — R$ 250,00 por pessoa</label>
                </div>

                <!-- Sem AC -->
                <h6 class="fw-bold mt-3">Alojamento Coletivo (Sem Ar Condicionado):</h6>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="acomodacao"
                           value="Alojamento 32 leitos - R$ 250,00">
                    <label class="form-check-label">Quarto coletivo com 32 leitos — R$ 250,00 por pessoa</label>
                </div>

                <!-- Barracas -->
                <h6 class="fw-bold mt-3">Barracas (Ar Livre ou Coberto):</h6>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="radio" name="acomodacao"
                           value="Barraca - R$ 250,00">
                    <label class="form-check-label">Espaço para barraca — R$ 250,00 por pessoa</label>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Enviar inscrição
                </button>

            </form>

            <?php endif; ?> <!-- fim do esconder formulário -->

        </div>
    </div>
</div>

<script>
// CPF máscara
document.getElementById('cpf').addEventListener('input', e => {
    let v = e.target.value.replace(/\D/g, '');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    e.target.value = v.substring(0, 14);
});

// Telefone máscara
document.getElementById('telefone').addEventListener('input', e => {
    let v = e.target.value.replace(/\D/g, '');
    if (v.length <= 10)
        v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
    else
        v = v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
    e.target.value = v;
});
</script>

<?php include '../cabecalho/footer_acampamento.php'; ?>
