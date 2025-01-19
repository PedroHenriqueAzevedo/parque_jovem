<?php
include(__DIR__ . '/../../../conexao/conexao.php');

function editarBanner($id, $dados, $arquivos) {
    global $conexao;

    $titulo = $dados['titulo'];
    $imagem = $arquivos['imagem'];

    try {
        if ($imagem['error'] === UPLOAD_ERR_OK) {
            $pastaDestino = '../../uploads/';
            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0755, true);
            }

            $caminhoImagem = $pastaDestino . basename($imagem['name']);
            if (!move_uploaded_file($imagem['tmp_name'], $caminhoImagem)) {
                return ['sucesso' => false, 'erro' => 'Erro ao salvar a nova imagem no servidor.'];
            }

            $stmt = $conexao->prepare("UPDATE banners SET titulo = :titulo, imagem = :imagem WHERE id = :id");
            $stmt->bindParam(':imagem', $caminhoImagem);
        } else {
            $stmt = $conexao->prepare("UPDATE banners SET titulo = :titulo WHERE id = :id");
        }

        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return ['sucesso' => true];
    } catch (PDOException $e) {
        return ['sucesso' => false, 'erro' => 'Erro ao atualizar banner: ' . $e->getMessage()];
    }
}
?>
