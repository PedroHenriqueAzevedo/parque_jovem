<?php
session_start();

header('Content-Type: application/json');

include(__DIR__ . '/../../../conexao/conexao.php');

// Função para buscar todas as imagens associadas a um projeto
function buscarImagensPorProjetoId($id) {
    global $conexao;
    $stmt = $conexao->prepare("SELECT caminho FROM projetos_fotos WHERE projeto_id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN); // Retorna apenas os caminhos das imagens
}

$response = ['success' => false, 'message' => 'Ocorreu um erro ao tentar excluir o projeto.'];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        // Buscar todas as imagens associadas ao projeto
        $imagens = buscarImagensPorProjetoId($id);

        // Excluir cada imagem do servidor
        foreach ($imagens as $imagem) {
            $filePath = __DIR__ . '/../../../' . $imagem;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Excluir as imagens do banco de dados
        $stmt = $conexao->prepare("DELETE FROM projetos_fotos WHERE projeto_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Excluir o projeto do banco de dados
        $stmt = $conexao->prepare("DELETE FROM projetos WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();


        $response['success'] = true;
        $response['message'] = 'Projeto e imagens excluídos com sucesso!';
    } catch (PDOException $e) {
        $response['message'] = 'Erro ao excluir projeto: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Erro: Nenhum ID fornecido para exclusão.';
}

echo json_encode($response);
exit;
?>
