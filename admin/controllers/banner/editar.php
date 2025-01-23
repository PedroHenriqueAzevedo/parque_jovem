<?php
include(__DIR__ . '/../../../conexao/conexao.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function editarBanner($id, $dados, $arquivos) {
    global $conexao;

    $titulo = $dados['titulo'];
    $imagem = $arquivos['imagem'];

    try {
        $pastaDestino = '../../uploads/';

        if ($imagem['error'] === UPLOAD_ERR_OK) {
            // Obter o nome da imagem atual do banco de dados antes da atualização
            $stmtImagemAtual = $conexao->prepare("SELECT imagem FROM banners WHERE id = :id");
            $stmtImagemAtual->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtImagemAtual->execute();
            $imagemAtual = $stmtImagemAtual->fetchColumn();

            // Se houver uma imagem existente, excluí-la
            if ($imagemAtual && file_exists($pastaDestino . $imagemAtual)) {
                unlink($pastaDestino . $imagemAtual);
            }

            // Obtendo apenas o nome do novo arquivo
            $nomeArquivo = basename($imagem['name']);
            $caminhoImagem = $pastaDestino . $nomeArquivo;

            if (!move_uploaded_file($imagem['tmp_name'], $caminhoImagem)) {
                return ['sucesso' => false, 'erro' => 'Erro ao salvar a nova imagem no servidor.'];
            }

            // Atualizar apenas o nome do arquivo no banco de dados
            $stmt = $conexao->prepare("UPDATE banners SET titulo = :titulo, imagem = :imagem WHERE id = :id");
            $stmt->bindParam(':imagem', $nomeArquivo);
        } else {
            $stmt = $conexao->prepare("UPDATE banners SET titulo = :titulo WHERE id = :id");
        }

        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Adicionando mensagem de sucesso após edição bem-sucedida
        $_SESSION['mensagem_sucesso'] = 'Banner editado com sucesso!';

        return ['sucesso' => true];
    } catch (PDOException $e) {
        return ['sucesso' => false, 'erro' => 'Erro ao atualizar banner: ' . $e->getMessage()];
    }
}
?>
