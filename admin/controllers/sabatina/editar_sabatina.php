<?php
include(__DIR__ . '/../../../conexao/conexao.php');

function editarArquivoSabatina($id, $dados, $arquivos) {
    global $conexao;

    $titulo = $dados['titulo'];
    $arquivo = $arquivos['arquivo'];

    try {
        if ($arquivo['error'] === UPLOAD_ERR_OK) {
            $pastaDestino = __DIR__ . '/../../../uploads/';
            $arquivoNome = time() . "_" . basename($arquivo['name']);
            $caminhoCompleto = $pastaDestino . $arquivoNome;

            if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
                return ['sucesso' => false, 'erro' => 'Erro ao salvar o novo arquivo no servidor.'];
            }

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
