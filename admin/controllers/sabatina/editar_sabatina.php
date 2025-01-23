<?php
include(__DIR__ . '/../../../conexao/conexao.php');

function editarArquivoSabatina($id, $dados, $arquivos) {
    global $conexao;

    $titulo = isset($dados['titulo']) ? trim($dados['titulo']) : '';
    $arquivo = isset($arquivos['arquivo']) ? $arquivos['arquivo'] : null;

    // Verificar se o título foi preenchido
    if (empty($titulo)) {
        return ['sucesso' => false, 'erro' => 'O título não pode estar vazio.'];
    }

    // Buscar o nome do arquivo atual para exclusão se necessário
    $stmt = $conexao->prepare("SELECT arquivo FROM escola_sabatina WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $arquivoAtual = $resultado['arquivo'] ?? null;

    if ($arquivo && $arquivo['error'] === UPLOAD_ERR_OK) {
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

        // Definir a pasta de destino e o novo nome para o arquivo
        $pastaDestino = __DIR__ . '/../../../uploads/';
        $arquivoNome = time() . "_" . basename($arquivo['name']);
        $caminhoCompleto = $pastaDestino . $arquivoNome;

        // Mover o novo arquivo para o diretório de destino
        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            return ['sucesso' => false, 'erro' => 'Erro ao salvar o novo arquivo no servidor.'];
        }

        // Excluir o arquivo antigo
        if ($arquivoAtual && file_exists($pastaDestino . $arquivoAtual)) {
            unlink($pastaDestino . $arquivoAtual);
        }
    }

    try {
        if ($arquivo) {
            $stmt = $conexao->prepare("UPDATE escola_sabatina SET titulo = :titulo, arquivo = :arquivo WHERE id = :id");
            $stmt->bindParam(':arquivo', $arquivoNome);
        } else {
            $stmt = $conexao->prepare("UPDATE escola_sabatina SET titulo = :titulo WHERE id = :id");
        }

        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return ['sucesso' => true];
    } catch (PDOException $e) {
        return ['sucesso' => false, 'erro' => 'Erro ao atualizar arquivo da escola sabatina: ' . $e->getMessage()];
    }
}
?>
