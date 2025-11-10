<?php
include '../conexao/conexao.php';
include '../cabecalho/header.php';

// Fuso horário de São Paulo (importante para Hostinger)
date_default_timezone_set('America/Sao_Paulo');

$mensagem = '';
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $data_nascimento = $_POST['data_nascimento'];
    $telefone = trim($_POST['telefone']);
    $igreja = $_POST['igreja'];
    $cep = trim($_POST['cep']);
    $rua = trim($_POST['rua']);
    $numero = trim($_POST['numero']);
    $bairro = trim($_POST['bairro']);
    $cidade = trim($_POST['cidade']);
    $estado = trim($_POST['estado']);

    // Verificação obrigatória
    if (
        empty($nome) || empty($cpf) || empty($data_nascimento) || empty($telefone) ||
        empty($igreja) || empty($cep) || empty($rua) || empty($numero) ||
        empty($bairro) || empty($cidade) || empty($estado)
    ) {
        $mensagem = "<div class='alert alert-danger text-center'>Preencha todos os campos obrigatórios.</div>";
    } else {
        // Verifica duplicidade
        $stmt = $conexao->prepare("SELECT id FROM inscricoes_acampamento WHERE nome = ? OR cpf = ? OR telefone = ?");
        $stmt->execute([$nome, $cpf, $telefone]);

        if ($stmt->fetch()) {
            $mensagem = "<div class='alert alert-danger text-center'>Já existe uma inscrição com esse nome, CPF ou telefone.</div>";
        } else {
            // Insere com data e fuso horário SP
            $data_cadastro = date('Y-m-d H:i:s');
            $stmt = $conexao->prepare("
                INSERT INTO inscricoes_acampamento 
                (nome, cpf, data_nascimento, telefone, igreja, cep, rua, numero, bairro, cidade, estado, data_cadastro)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$nome, $cpf, $data_nascimento, $telefone, $igreja, $cep, $rua, $numero, $bairro, $cidade, $estado, $data_cadastro])) {
                header("Location: ../index.php");
                exit;
            } else {
                $mensagem = "<div class='alert alert-danger text-center'>Erro ao salvar a inscrição. Tente novamente.</div>";
            }
        }
    }
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

<div class="container mt-5 mb-5">
    <div class="card shadow">
        <div class="card-body">
            <a href="../index.php" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>

            <h3 class="card-title mb-4 text-center">Inscrição para o Acampamento</h3>

            <?= $mensagem ?>

            <form method="POST" id="form-inscricao" novalidate>
                <div class="mb-3">
                    <label class="form-label">Nome completo:</label>
                    <input type="text" name="nome" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">CPF:</label>
                    <input type="text" name="cpf" id="cpf" class="form-control" maxlength="14" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Data de nascimento:</label>
                    <input type="date" name="data_nascimento" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Telefone:</label>
                    <input type="text" name="telefone" id="telefone" class="form-control" maxlength="15" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Igreja:</label>
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
                <h5 class="mt-3 mb-3 text-center">Endereço</h5>

                <div class="alert alert-info text-center mb-3">
                    ⚠️ Este formulário utiliza a API ViaCEP — ao digitar o CEP, os campos de endereço serão preenchidos automaticamente.
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">CEP:</label>
                        <input type="text" name="cep" id="cep" class="form-control" maxlength="9" required>
                        <small id="cep-status" class="text-muted"></small>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Rua:</label>
                        <input type="text" name="rua" id="rua" class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Número:</label>
                        <input type="text" name="numero" id="numero" class="form-control" required>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Bairro:</label>
                        <input type="text" name="bairro" id="bairro" class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Cidade:</label>
                        <input type="text" name="cidade" id="cidade" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Estado (UF):</label>
                        <input type="text" name="estado" id="estado" class="form-control" maxlength="2" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Enviar inscrição</button>
            </form>
        </div>
    </div>
</div>

<script>
// Máscara CPF
document.getElementById('cpf').addEventListener('input', e => {
    let v = e.target.value.replace(/\D/g, '');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    e.target.value = v.substring(0, 14);
});

// Máscara telefone
document.getElementById('telefone').addEventListener('input', e => {
    let v = e.target.value.replace(/\D/g, '');
    if (v.length <= 10) {
        v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
    } else {
        v = v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
    }
    e.target.value = v;
});

// Máscara CEP + API ViaCEP
const cepInput = document.getElementById('cep');
const statusCep = document.getElementById('cep-status');

cepInput.addEventListener('keypress', e => {
    if (e.key === 'Enter') e.preventDefault(); // Evita submit ao pressionar Enter
});

cepInput.addEventListener('input', e => {
    let v = e.target.value.replace(/\D/g, ''); // remove tudo que não é número
    v = v.replace(/(\d{5})(\d)/, '$1-$2');
    e.target.value = v.substring(0, 9);

    // Remove o hífen antes da requisição
    const cepLimpo = v.replace('-', '');

    if (cepLimpo.length === 8) {
        statusCep.textContent = '🔎 Buscando endereço...';
        fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`)
            .then(resp => resp.json())
            .then(data => {
                if (!data.erro) {
                    document.getElementById('rua').value = data.logradouro || '';
                    document.getElementById('bairro').value = data.bairro || '';
                    document.getElementById('cidade').value = data.localidade || '';
                    document.getElementById('estado').value = data.uf || '';
                    statusCep.textContent = '✅ Endereço encontrado!';
                } else {
                    statusCep.textContent = '⚠️ CEP não encontrado.';
                }
            })
            .catch(() => {
                statusCep.textContent = '⚠️ Erro ao buscar o CEP.';
            });
    } else {
        statusCep.textContent = '';
    }
});
</script>

<?php include '../cabecalho/footer.php'; ?>
