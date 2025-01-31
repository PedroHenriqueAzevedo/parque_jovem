<?php
include(__DIR__ . '/../../../conexao/conexao.php');

function editarProjeto($id, $dados, $arquivos)
{
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

        // Excluir imagens removidas pelo usuário
        if (!empty($dados['imagens_excluidas'])) {
            $imagensExcluidas = explode(',', $dados['imagens_excluidas']);
            foreach ($imagensExcluidas as $nomeImagem) {
                // Buscar imagem pelo nome
                $stmt = $conexao->prepare("SELECT id, caminho FROM projetos_fotos WHERE caminho LIKE :nomeImagem AND projeto_id = :projeto_id");
                $stmt->bindValue(':nomeImagem', "%$nomeImagem%", PDO::PARAM_STR);
                $stmt->bindValue(':projeto_id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $imagem = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($imagem) {
                    $caminhoCompleto = __DIR__ . '/../../../' . $imagem['caminho'];
                    if (file_exists($caminhoCompleto)) {
                        unlink($caminhoCompleto);
                    }

                    // Remover do banco de dados
                    $stmt = $conexao->prepare("DELETE FROM projetos_fotos WHERE id = :imagem_id");
                    $stmt->bindParam(':imagem_id', $imagem['id'], PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
        }

        // Adicionar novas imagens se houver upload
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

                // Criar nome único para evitar conflitos
                $nomeImagem = time() . "_" . uniqid() . "." . $extensaoImagem;
                $caminhoCompleto = $pastaDestino . $nomeImagem;
                $caminhoRelativo = 'uploads/' . $nomeImagem;

                if (move_uploaded_file($arquivos['fotos']['tmp_name'][$key], $caminhoCompleto)) {
                    // Inserir no banco de dados
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
?>
