<?php
session_start();
header('Content-Type: application/json');

include(__DIR__ . '/../../../conexao/conexao.php');

// Função para buscar um arquivo específico no banco de dados
function buscarArquivoPorId($id) {
    global $conexao;
    $stmt = $conexao->prepare("SELECT arquivo FROM escola_sabatina WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$response = ['success' => false, 'message' => 'Ocorreu um erro ao tentar excluir o arquivo.'];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        $arquivo = buscarArquivoPorId($id);

        if ($arquivo && isset($arquivo['arquivo'])) {
            // Caminho atualizado para apontar corretamente para a raiz do site
            $caminhoArquivo = realpath(__DIR__ . '/../../../uploads/' . $arquivo['arquivo']);


            // Verificar se o arquivo existe e tentar excluí-lo
            if (file_exists($caminhoArquivo)) {
                if (unlink($caminhoArquivo)) {
                    $stmt = $conexao->prepare("DELETE FROM escola_sabatina WHERE id = :id");
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();

                    $response['success'] = true;
                    $response['message'] = 'Arquivo excluído com sucesso!';
                } else {
                    $response['message'] = 'Falha ao excluir o arquivo físico.';
                }
            } else {
                // Arquivo físico não encontrado, mas registro ainda existe no banco
                $stmt = $conexao->prepare("DELETE FROM escola_sabatina WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                $response['success'] = true;
                $response['message'] = 'Arquivo não encontrado, mas registro removido do banco de dados.';
            }
        } else {
            $response['message'] = 'Erro: Arquivo não encontrado no banco de dados.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Erro ao excluir arquivo: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Erro: Nenhum ID fornecido para exclusão.';
}

echo json_encode($response);
exit;
?>
