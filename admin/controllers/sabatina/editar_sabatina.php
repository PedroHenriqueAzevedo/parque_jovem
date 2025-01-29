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

    // Buscar o caminho do arquivo atual
    $stmt = $conexao->prepare("SELECT arquivo FROM escola_sabatina WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $arquivoAtual = $resultado['arquivo'] ?? null;

    $arquivoNome = $arquivoAtual; // Mantém o arquivo atual caso nenhum novo seja enviado

    if ($arquivo && $arquivo['error'] === UPLOAD_ERR_OK) {
        // Validar tipo de arquivo permitido
        $extensoesPermitidas = ['pdf', 'doc', 'docx', 'jpg', 'png'];
        $extensaoArquivo = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

        if (!in_array($extensaoArquivo, $extensoesPermitidas)) {
            return ['sucesso' => false, 'erro' => 'Tipo de arquivo não permitido. Apenas PDF, DOC, JPG ou PNG são aceitos.'];
        }

        // Limitar tamanho do arquivo (30MB)
        $tamanhoMaximo = 30 * 1024 * 1024;
        if ($arquivo['size'] > $tamanhoMaximo) {
            return ['sucesso' => false, 'erro' => 'O arquivo excede o tamanho máximo permitido de 30MB.'];
        }

        // Definir a pasta de destino e o novo nome para o arquivo
        $pastaDestino = __DIR__ . '/../../../uploads/';
        $novoNomeArquivo = time() . "_" . basename($arquivo['name']);
        $caminhoCompleto = $pastaDestino . $novoNomeArquivo;

        // Criar a pasta caso não exista
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0755, true);
        }

        // Mover o novo arquivo para o diretório de destino
        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            return ['sucesso' => false, 'erro' => 'Erro ao salvar o novo arquivo no servidor.'];
        }

        // Excluir o arquivo antigo, se houver um novo
        if ($arquivoAtual && file_exists(__DIR__ . '/../../../' . $arquivoAtual)) {
            unlink(__DIR__ . '/../../../' . $arquivoAtual);
        }

        $arquivoNome = 'uploads/' . $novoNomeArquivo;
    }

    try {
        // Atualizar a entrada mantendo o arquivo se nenhum novo foi enviado
        $stmt = $conexao->prepare("UPDATE escola_sabatina SET titulo = :titulo, arquivo = :arquivo WHERE id = :id");
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':arquivo', $arquivoNome);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return ['sucesso' => true];
    } catch (PDOException $e) {
        return ['sucesso' => false, 'erro' => 'Erro ao atualizar arquivo da escola sabatina: ' . $e->getMessage()];
    }
}
?>
