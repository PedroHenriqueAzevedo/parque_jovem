<?php
include(__DIR__ . '/../../../conexao/conexao.php');

function adicionarArquivoEscolaSabatina($dados, $arquivos) {
    global $conexao;

    // Receber o título e o arquivo enviado
    $titulo = $dados['titulo'];
    $arquivo = $arquivos['arquivo'];

    // Verificar se o arquivo foi enviado corretamente
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        return ['sucesso' => false, 'erro' => 'Erro no envio do arquivo.'];
    }

    // Definir a pasta única para armazenamento
    $pastaDestino = __DIR__ . '/../../../uploads/';
    $arquivoNome = time() . "_" . basename($arquivo['name']);
    $caminhoCompleto = $pastaDestino . $arquivoNome;

    // Criar a pasta caso não exista
    if (!is_dir($pastaDestino)) {
        mkdir($pastaDestino, 0755, true);
    }

    // Mover o arquivo para o diretório de destino
    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
        return ['sucesso' => false, 'erro' => 'Erro ao salvar o arquivo no servidor.'];
    }

    try {
        // Salvar apenas o nome do arquivo no banco de dados
        $stmt = $conexao->prepare("INSERT INTO escola_sabatina (titulo, arquivo) VALUES (:titulo, :arquivo)");
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':arquivo', $arquivoNome);
        $stmt->execute();

        return ['sucesso' => true];
    } catch (PDOException $e) {
        return ['sucesso' => false, 'erro' => 'Erro ao salvar no banco: ' . $e->getMessage()];
    }
}
?>
