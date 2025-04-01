<?php 
include '../cabecalho/header.php'; 
include '../conexao/conexao.php';

$sucesso = false;
$id_cadastro = null;
$nome_cadastro = null;
$mensagem_erro = "";

// NOVO: recuperação por telefone apenas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recuperar'])) {
    $telefone_busca = $_POST['telefone_busca'];

    $stmt = $conexao->prepare("SELECT id, nome FROM cadastros_jovens WHERE telefone = ?");
    $stmt->execute([$telefone_busca]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        $id_cadastro = $usuario['id'];
        $nome_cadastro = $usuario['nome'];
        $sucesso = true;
    } else {
        echo "<script>alert('Cadastro não encontrado. Verifique o telefone informado.');</script>";
    }
}

// CADASTRO NOVO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['recuperar'])) {
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $tipo_cadastro = $_POST['tipo_cadastro'];
    $adventista = $_POST['adventista'];
    $igreja = $adventista === 'Sim' ? $_POST['igreja'] : null;

    $erro_nome = false;
    $erro_telefone = false;

    // Verificar nome
    $stmt_nome = $conexao->prepare("SELECT id FROM cadastros_jovens WHERE nome = ?");
    $stmt_nome->execute([$nome]);
    if ($stmt_nome->fetch()) {
        $erro_nome = true;
    }

    // Verificar telefone
    $stmt_tel = $conexao->prepare("SELECT id FROM cadastros_jovens WHERE telefone = ?");
    $stmt_tel->execute([$telefone]);
    if ($stmt_tel->fetch()) {
        $erro_telefone = true;
    }

    if ($erro_nome && $erro_telefone) {
        $mensagem_erro = "Já existe um cadastro com esse nome e telefone.";
    } elseif ($erro_nome) {
        $mensagem_erro = "Já existe um cadastro com esse nome.";
    } elseif ($erro_telefone) {
        $mensagem_erro = "Já existe um cadastro com esse telefone.";
    } else {
        $stmt = $conexao->prepare("INSERT INTO cadastros_jovens (nome, telefone, tipo_cadastro, adventista, igreja) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $telefone, $tipo_cadastro, $adventista, $igreja]);

        $id_cadastro = $conexao->lastInsertId();
        $nome_cadastro = $nome;
        $sucesso = true;

        header("Location: cadastro.php?id=$id_cadastro&nome=" . urlencode($nome));
        exit();
    }
}

if (isset($_GET['id']) && isset($_GET['nome'])) {
    $id_cadastro = $_GET['id'];
    $nome_cadastro = $_GET['nome'];
    $sucesso = true;
}
?>

<div class="container mt-5 mb-5">
    <div class="card shadow">
        <div class="card-body">
            <a href="../index.php" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>

            <h3 class="card-title mb-4 text-center">Cadastros de Jovens</h3>

            <div id="mensagem-cadastro">
            <?php if ($sucesso): ?>
                <div class="alert alert-success text-center">
                    <h4 class="text-success">Parabéns, <?= htmlspecialchars($nome_cadastro) ?>!</h4>
                    <p>Seu número de cadastro é:</p>
                    <h1 class="display-3 fw-bold text-dark"><?= htmlspecialchars($id_cadastro) ?></h1>
                    <p class="mt-3">Os coordenadores dos jovens entrarão em contato com você via WhatsApp.</p>
                </div>

                <script>
                    localStorage.setItem('cadastro_id', '<?= $id_cadastro ?>');
                    localStorage.setItem('cadastro_nome', '<?= addslashes($nome_cadastro) ?>');
                </script>
            <?php else: ?>
                <?php if (!empty($mensagem_erro)): ?>
                    <div class="alert alert-danger text-center" id="mensagem-erro">
                        <?= $mensagem_erro ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nome completo:</label>
                        <input type="text" class="form-control nome" name="nome" placeholder="Ex: João da Silva" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Telefone (com DDD):</label>
                        <input type="text" class="form-control telefone" name="telefone" placeholder="Ex: (11) 91234-5678" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">O que você deseja?</label>
                        <select class="form-select" name="tipo_cadastro" required>
                            <option value="">Selecione</option>
                            <option value="Tenho interesse em me batizar">Tenho interesse em me batizar</option>
                            <option value="Quero oração">Quero oração</option>
                            <option value="Quero participar da classe bíblica">Quero participar da classe bíblica</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Você é da Igreja Adventista?</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="adventista" value="Sim" required onclick="document.getElementById('campo_igreja').style.display='block'">
                            <label class="form-check-label">Sim</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="adventista" value="Não" required onclick="document.getElementById('campo_igreja').style.display='none'">
                            <label class="form-check-label">Não</label>
                        </div>
                    </div>

                    <div class="mb-3" id="campo_igreja" style="display:none;">
                        <label class="form-label">Qual igreja?</label>
                        <input type="text" class="form-control" name="igreja" placeholder="Ex: IASD Parque São Domingos">
                    </div>

                    <button type="submit" class="btn btn-primary">Enviar</button>

                    <div class="mt-4 text-center">
                        <a href="#" class="btn btn-link" onclick="mostrarBusca()">Já me cadastrei</a>
                    </div>
                </form>

                <div id="busca-cadastro" style="display:none;" class="mt-4">
                    <h5 class="text-center">Recuperar número de cadastro</h5>
                    <form method="POST">
                        <input type="hidden" name="recuperar" value="1">

                        <div class="mb-3">
                            <label class="form-label">Telefone (com DDD):</label>
                            <input type="text" class="form-control telefone" name="telefone_busca" placeholder="Ex: (11) 91234-5678" required>
                        </div>

                        <button type="submit" class="btn btn-outline-primary">Buscar</button>
                    </form>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Máscara para todos os campos de telefone
const telefones = document.querySelectorAll('.telefone');
telefones.forEach(input => {
    input.addEventListener('input', function (e) {
        let valor = e.target.value.replace(/\D/g, '').substring(0, 11);
        if (valor.length <= 10) {
            e.target.value = valor.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        } else {
            e.target.value = valor.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
        }
    });
});

// Remove alertas após 4 segundos (exceto sucesso)
setTimeout(() => {
    document.querySelectorAll('.alert-danger, .alert-warning').forEach(el => el.remove());
}, 4000);

// Verifica se há dados salvos no navegador
const nomeSalvo = localStorage.getItem('cadastro_nome');
const idSalvo = localStorage.getItem('cadastro_id');

// Se sim, mostra a mensagem de sucesso
if (nomeSalvo && idSalvo && !window.location.search.includes('id=')) {
    document.getElementById('mensagem-cadastro').innerHTML = `
        <div class="alert alert-success text-center">
            <h4 class="text-success">Olá ${nomeSalvo}, você já está cadastrado!</h4>
            <p>Seu número de cadastro é:</p>
            <h1 class="display-3 fw-bold text-dark">${idSalvo}</h1>
            <p class="mt-3">Os coordenadores dos jovens entrarão em contato com você via WhatsApp.</p>
        </div>
    `;
}

// Função para mostrar a área de busca
function mostrarBusca() {
    document.querySelector("form[method='POST']").style.display = 'none';
    document.getElementById('busca-cadastro').style.display = 'block';
}
</script>


<?php include '../cabecalho/footer.php'; ?>