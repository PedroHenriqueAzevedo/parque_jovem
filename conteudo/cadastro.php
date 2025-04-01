<?php 
include '../conexao/conexao.php';

// AJAX para localStorage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verificar_ajax'])) {
    header('Content-Type: application/json');
    $id = $_POST['id'] ?? null;
    $nome = $_POST['nome'] ?? null;

    if (!$id || !$nome) {
        echo json_encode(['valido' => false]);
        exit;
    }

    $stmt = $conexao->prepare("SELECT id FROM cadastros_jovens WHERE id = ? AND nome = ?");
    $stmt->execute([$id, $nome]);
    $existe = $stmt->fetch();

    echo json_encode(['valido' => $existe ? true : false]);
    exit;
}

include '../cabecalho/header.php';

$sucesso = false;
$id_cadastro = null;
$nome_cadastro = null;
$mensagem_erro = "";
$mensagem_recuperacao = "";
$exibir_busca = false;

// RECUPERAÇÃO
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
        $mensagem_recuperacao = "Cadastro não encontrado. Verifique o telefone informado.";
        $exibir_busca = true;
    }
}

// CADASTRO NOVO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['recuperar']) && !isset($_POST['verificar_ajax'])) {
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $tipo_cadastro = $_POST['tipo_cadastro'];
    $adventista = $_POST['adventista'];
    $igreja = $adventista === 'Sim' ? $_POST['igreja'] : null;

    $erro_nome = false;
    $erro_telefone = false;

    $stmt_nome = $conexao->prepare("SELECT id FROM cadastros_jovens WHERE nome = ?");
    $stmt_nome->execute([$nome]);
    if ($stmt_nome->fetch()) $erro_nome = true;

    $stmt_tel = $conexao->prepare("SELECT id FROM cadastros_jovens WHERE telefone = ?");
    $stmt_tel->execute([$telefone]);
    if ($stmt_tel->fetch()) $erro_telefone = true;

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
                    <div class="alert alert-danger text-center" id="mensagem-erro"><?= $mensagem_erro ?></div>
                <?php endif; ?>

                <form method="POST" id="form-cadastro" style="<?= $exibir_busca ? 'display: none;' : '' ?>">
                    <div class="mb-3">
                        <label class="form-label">Nome completo:</label>
                        <input type="text" class="form-control nome" name="nome" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Telefone (com DDD):</label>
                        <input type="text" class="form-control telefone" name="telefone" required>
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
                        <input type="text" class="form-control" name="igreja">
                    </div>

                    <button type="submit" class="btn btn-primary">Enviar</button>

                    <div class="mt-4 text-center">
                        <a href="#" class="btn btn-link" onclick="mostrarBusca()">Já me cadastrei</a>
                    </div>
                </form>

                <div id="busca-cadastro" class="mt-4" style="<?= $exibir_busca ? '' : 'display: none;' ?>">
                    <h5 class="text-center">Recuperar número de cadastro</h5>

                    <?php if (!empty($mensagem_recuperacao)): ?>
                        <div class="alert alert-danger text-center" id="mensagem-recuperacao">
                            <?= $mensagem_recuperacao ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="recuperar" value="1">

                        <div class="mb-3">
                            <label class="form-label">Telefone (com DDD):</label>
                            <input type="text" class="form-control telefone" name="telefone_busca" required>
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
// Máscara para telefone
document.querySelectorAll('.telefone').forEach(input => {
    input.addEventListener('input', function (e) {
        let valor = e.target.value.replace(/\D/g, '').substring(0, 11);
        e.target.value = valor.length <= 10 
            ? valor.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3')
            : valor.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
    });
});

// Remove alertas após 4s
setTimeout(() => {
    document.querySelectorAll('.alert-danger, .alert-warning').forEach(el => el.remove());
}, 4000);

// Verifica localStorage
const nomeSalvo = localStorage.getItem('cadastro_nome');
const idSalvo = localStorage.getItem('cadastro_id');

if (nomeSalvo && idSalvo && !window.location.search.includes('id=')) {
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `verificar_ajax=1&id=${encodeURIComponent(idSalvo)}&nome=${encodeURIComponent(nomeSalvo)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.valido) {
            document.getElementById('mensagem-cadastro').innerHTML = `
                <div class="alert alert-success text-center">
                    <h4 class="text-success">Olá ${nomeSalvo}, você já está cadastrado!</h4>
                    <p>Seu número de cadastro é:</p>
                    <h1 class="display-3 fw-bold text-dark">${idSalvo}</h1>
                    <p class="mt-3">Os coordenadores dos jovens entrarão em contato com você via WhatsApp.</p>
                </div>
            `;
        } else {
            localStorage.removeItem('cadastro_id');
            localStorage.removeItem('cadastro_nome');
        }
    });
}

function mostrarBusca() {
    document.getElementById('form-cadastro').style.display = 'none';
    document.getElementById('busca-cadastro').style.display = 'block';
}
</script>

<?php include '../cabecalho/footer.php'; ?>
