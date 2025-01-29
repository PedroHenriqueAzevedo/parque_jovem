<?php
include(__DIR__ . '/../../../conexao/conexao.php');

function editarProjeto($id, $dados, $arquivos) {
    global $conexao;

    $id = intval($id);
    if ($id <= 0) {
        return ['sucesso' => false, 'erro' => 'ID inválido do projeto.'];
    }

    $nome = isset($dados['nome']) ? trim($dados['nome']) : '';
    $conteudo = isset($dados['conteudo']) ? trim($dados['conteudo']) : '';

    if (empty($nome) || empty($conteudo)) {
        return ['sucesso' => false, 'erro' => 'Nome e descrição são obrigatórios.'];
    }

    try {
        // Atualizar os dados do projeto
        $stmt = $conexao->prepare("UPDATE projetos SET nome = :nome, conteudo = :conteudo WHERE id = :id");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':conteudo', $conteudo);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Verificar se há novas imagens para upload
        if (!empty($arquivos['fotos']['name'][0])) {
            $pastaDestino = __DIR__ . '/../../../uploads/';
            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0755, true);
            }

            foreach ($arquivos['fotos']['name'] as $key => $nomeArquivo) {
                $extensaoImagem = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
                if (!in_array($extensaoImagem, ['jpg', 'jpeg', 'png', 'gif'])) {
                    continue;
                }

                $tamanhoMaximo = 5 * 1024 * 1024; // 5MB
                if ($arquivos['fotos']['size'][$key] > $tamanhoMaximo) {
                    continue;
                }

                $nomeImagem = time() . "_" . basename($nomeArquivo);
                $caminhoCompleto = $pastaDestino . $nomeImagem;
                $caminhoRelativo = 'uploads/' . $nomeImagem;

                if (move_uploaded_file($arquivos['fotos']['tmp_name'][$key], $caminhoCompleto)) {
                    $stmtImagem = $conexao->prepare("INSERT INTO projetos_fotos (projeto_id, caminho) VALUES (:projeto_id, :caminho)");
                    $stmtImagem->bindParam(':projeto_id', $id, PDO::PARAM_INT);
                    $stmtImagem->bindParam(':caminho', $caminhoRelativo);
                    $stmtImagem->execute();
                }
            }
        }

        return ['sucesso' => true];
    } catch (PDOException $e) {
        return ['sucesso' => false, 'erro' => 'Erro ao atualizar projeto: ' . $e->getMessage()];
    }
}

// Processar exclusão de imagem via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_imagem'])) {
    $imagemId = intval($_POST['excluir_imagem']);
    global $conexao;

    try {
        $stmt = $conexao->prepare("SELECT caminho FROM projetos_fotos WHERE id = :imagem_id");
        $stmt->bindParam(':imagem_id', $imagemId, PDO::PARAM_INT);
        $stmt->execute();
        $imagem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($imagem) {
            $caminhoCompleto = __DIR__ . '/../../../' . $imagem['caminho'];
            if (file_exists($caminhoCompleto)) {
                unlink($caminhoCompleto);
            }

            $stmt = $conexao->prepare("DELETE FROM projetos_fotos WHERE id = :imagem_id");
            $stmt->bindParam(':imagem_id', $imagemId, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['sucesso' => true]);
            exit;
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Imagem não encontrada.']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao excluir a imagem: ' . $e->getMessage()]);
        exit;
    }
}
?>
