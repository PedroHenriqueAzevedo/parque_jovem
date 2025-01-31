<?php
include(__DIR__ . '/../../../conexao/conexao.php');

function adicionarProjetoComImagens($dados, $arquivos) {
    global $conexao;

    $nome = isset($dados['nome']) ? trim($dados['nome']) : '';
    $conteudo = isset($dados['conteudo']) ? trim($dados['conteudo']) : '';

    if (empty($nome) || empty($conteudo)) {
        return ['sucesso' => false, 'erro' => 'Preencha todos os campos obrigatórios.'];
    }
    // Excluir imagens removidas pelo usuário
if (!empty($dados['imagens_excluidas'])) {
    $imagensExcluidas = explode(',', $dados['imagens_excluidas']);
    foreach ($imagensExcluidas as $nomeImagem) {
        $stmt = $conexao->prepare("SELECT id, caminho FROM projetos_fotos WHERE caminho LIKE :nomeImagem");
        $stmt->bindValue(':nomeImagem', "%$nomeImagem%", PDO::PARAM_STR);
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



    try {
        $projeto_id = isset($dados['id']) ? intval($dados['id']) : null;

        if ($projeto_id) {
            $stmt = $conexao->prepare("UPDATE projetos SET nome = :nome, conteudo = :conteudo WHERE id = :id");
            $stmt->bindParam(':id', $projeto_id, PDO::PARAM_INT);
        } else {
            $stmt = $conexao->prepare("INSERT INTO projetos (nome, conteudo) VALUES (:nome, :conteudo)");
        }

        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':conteudo', $conteudo);
        $stmt->execute();

        if (!$projeto_id) {
            $projeto_id = $conexao->lastInsertId();
        }

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

                $tamanhoMaximo = 5 * 1024 * 1024;
                if ($arquivos['fotos']['size'][$key] > $tamanhoMaximo) {
                    continue;
                }

                $nomeImagem = time() . "_" . basename($nomeArquivo);
                $caminhoCompleto = $pastaDestino . $nomeImagem;
                $caminhoRelativo = 'uploads/' . $nomeImagem;

                if (move_uploaded_file($arquivos['fotos']['tmp_name'][$key], $caminhoCompleto)) {
                    $stmtImagem = $conexao->prepare("INSERT INTO projetos_fotos (projeto_id, caminho) VALUES (:projeto_id, :caminho)");
                    $stmtImagem->bindParam(':projeto_id', $projeto_id);
                    $stmtImagem->bindParam(':caminho', $caminhoRelativo);
                    $stmtImagem->execute();
                }
            }
        }

        return ['sucesso' => true];
    } catch (PDOException $e) {
        return ['sucesso' => false, 'erro' => 'Erro ao salvar no banco: ' . $e->getMessage()];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start(); // Iniciar a sessão para usar mensagens de sessão
    $resultado = adicionarProjetoComImagens($_POST, $_FILES);

    if ($resultado['sucesso']) {
        // Configurar mensagem de sucesso na sessão
        $_SESSION['mensagem_sucesso'] = 'Projeto adicionado com sucesso!';
        header('Location: listar.php');
        exit;
    } else {
        // Configurar mensagem de erro para exibir na página atual
        $_SESSION['mensagem_erro'] = $resultado['erro'];
        header('Location: adicionar.php'); // Redirecionar de volta para evitar reenvio do formulário
        exit;
    }
}
?>
