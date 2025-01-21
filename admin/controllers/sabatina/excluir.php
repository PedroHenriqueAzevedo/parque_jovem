<?php
session_start();
header('Content-Type: application/json');

include(__DIR__ . '/../../../conexao/conexao.php');
require_once __DIR__ . '/buscar_sabatina.php';

$response = ['success' => false, 'message' => 'Ocorreu um erro ao tentar excluir o arquivo.'];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $arquivo = buscarArquivosSabatina($id);
        $caminhoArquivo = __DIR__ . '/../../../uploads/' . $arquivo['arquivo'];

        if ($arquivo && file_exists($caminhoArquivo)) {
            unlink($caminhoArquivo);
        }

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
