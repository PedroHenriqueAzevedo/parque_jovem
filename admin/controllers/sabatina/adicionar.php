<?php
include(__DIR__ . '/../../../conexao/conexao.php');

function adicionarArquivoEscolaSabatina($dados, $arquivos) {
    global $conexao;

    // Inicializar variáveis para evitar warnings
    $titulo = isset($dados['titulo']) ? trim($dados['titulo']) : '';
    $arquivo = isset($arquivos['arquivo']) ? $arquivos['arquivo'] : null;

    // Verificar se o título foi preenchido
    if (empty($titulo)) {
        return ['sucesso' => false, 'erro' => 'Erro no envio do arquivo. Certifique-se de selecionar um arquivo válido.'];
    }

    // Verificar se o arquivo foi enviado corretamente
    if (!$arquivo || $arquivo['error'] !== UPLOAD_ERR_OK) {
        return ['sucesso' => false, 'erro' => 'Erro no envio do arquivo. Certifique-se de selecionar um arquivo válido.'];
    }

    // Validar tipo de arquivo permitido
    $extensoesPermitidas = ['pdf', 'doc', 'docx', 'jpg', 'png'];
    $extensaoArquivo = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

    if (!in_array($extensaoArquivo, $extensoesPermitidas)) {
        return ['sucesso' => false, 'erro' => 'Tipo de arquivo não permitido. Apenas PDF, DOC, JPG ou PNG são aceitos.'];
    }

    // Limitar tamanho do arquivo (5MB)
    $tamanhoMaximo = 5 * 1024 * 1024;
    if ($arquivo['size'] > $tamanhoMaximo) {
        return ['sucesso' => false, 'erro' => 'O arquivo excede o tamanho máximo permitido de 5MB.'];
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
        return ['sucesso' => false, 'erro' => 'Erro ao salvar o arquivo no servidor. Tente novamente.'];
    }

    // Caminho relativo para salvar no banco de dados
    $caminhoRelativo = 'uploads/' . $arquivoNome;

    try {
        // Salvar o caminho completo no banco de dados
        $stmt = $conexao->prepare("INSERT INTO escola_sabatina (titulo, arquivo) VALUES (:titulo, :arquivo)");
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':arquivo', $caminhoRelativo);
        $stmt->execute();

        return ['sucesso' => true];
    } catch (PDOException $e) {
        return ['sucesso' => false, 'erro' => 'Erro ao salvar no banco de dados.'];
    }
}
?>
