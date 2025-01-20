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

    // Diretório de destino para armazenar o arquivo
    $pastaDestino = '../../uploads/escola_sabatina/';
    
    // Verificar se o diretório existe, caso contrário, criar
    if (!is_dir($pastaDestino)) {
        mkdir($pastaDestino, 0755, true);
    }

    // Caminho do arquivo a ser salvo
    $caminhoArquivo = $pastaDestino . basename($arquivo['name']);
    
    // Tentar mover o arquivo para o diretório de destino
    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoArquivo)) {
        return ['sucesso' => false, 'erro' => 'Erro ao salvar o arquivo no servidor.'];
    }

    try {
        // Inserir dados no banco de dados
        $stmt = $conexao->prepare("INSERT INTO escola_sabatina (titulo, arquivo) VALUES (:titulo, :arquivo)");
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':arquivo', $caminhoArquivo);
        $stmt->execute();

        return ['sucesso' => true];
    } catch (PDOException $e) {
        return ['sucesso' => false, 'erro' => 'Erro ao salvar no banco: ' . $e->getMessage()];
    }
}
?>
