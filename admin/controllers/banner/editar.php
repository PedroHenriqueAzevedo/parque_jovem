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
        if ($imagem['error'] === UPLOAD_ERR_OK) {
            $pastaDestino = '../../uploads/';
            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0755, true);
            }

            // Obtendo apenas o nome do arquivo
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
