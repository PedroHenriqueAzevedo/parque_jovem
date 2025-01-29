<?php
include(__DIR__ . '/../../../conexao/conexao.php');

function buscarProjetos() {
    global $conexao;
    try {
        // Buscar todos os projetos
        $stmt = $conexao->prepare("SELECT * FROM projetos ORDER BY id DESC");
        $stmt->execute();
        $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Buscar imagens associadas a cada projeto
        foreach ($projetos as $key => $projeto) {
            $stmtFotos = $conexao->prepare("SELECT caminho FROM projetos_fotos WHERE projeto_id = :projeto_id");
            $stmtFotos->bindParam(':projeto_id', $projeto['id'], PDO::PARAM_INT);
            $stmtFotos->execute();
            $projetos[$key]['fotos'] = $stmtFotos->fetchAll(PDO::FETCH_COLUMN); // Retorna apenas os caminhos das imagens
        }

        return $projetos;
    } catch (PDOException $e) {
        die("Erro ao buscar projetos: " . $e->getMessage());
    }
}

function buscarProjetoPorId($id) {
    global $conexao;
    try {
        // Buscar um projeto específico
        $stmt = $conexao->prepare("SELECT * FROM projetos WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $projeto = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se o projeto existir, buscar suas imagens
        if ($projeto) {
            $stmtFotos = $conexao->prepare("SELECT caminho FROM projetos_fotos WHERE projeto_id = :projeto_id");
            $stmtFotos->bindParam(':projeto_id', $id, PDO::PARAM_INT);
            $stmtFotos->execute();
            $projeto['fotos'] = $stmtFotos->fetchAll(PDO::FETCH_COLUMN); // Retorna apenas os caminhos das imagens
        }

        return $projeto;
    } catch (PDOException $e) {
        die("Erro ao buscar projeto por ID: " . $e->getMessage());
    }
}
?>
