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