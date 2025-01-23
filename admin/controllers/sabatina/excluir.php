<?php
session_start();

header('Content-Type: application/json');

include(__DIR__ . '/../../../conexao/conexao.php');

// Função para buscar o arquivo por ID
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
        // Buscar os detalhes do arquivo pelo ID
        $arquivo = buscarArquivoPorId($id);

        // Verificar se o arquivo existe e excluí-lo
        if ($arquivo && isset($arquivo['arquivo'])) {
            $filePath = __DIR__ . '/../../../' . $arquivo['arquivo'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Excluir o registro do banco de dados
        $stmt = $conexao->prepare("DELETE FROM escola_sabatina WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $response['success'] = true;
        $response['message'] = 'Arquivo excluído com sucesso!';
    } catch (PDOException $e) {
        $response['message'] = 'Erro ao excluir arquivo: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Erro: Nenhum ID fornecido para exclusão.';
}

echo json_encode($response);
exit;
?>
