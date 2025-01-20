<?php
session_start();

header('Content-Type: application/json');

include(__DIR__ . '/../../../conexao/conexao.php');
require_once __DIR__ . '/buscar.php';

$response = ['success' => false, 'message' => 'Ocorreu um erro ao tentar excluir o banner.'];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $banner = buscarBannerPorId($id);
        if ($banner && file_exists($banner['imagem'])) {
            unlink($banner['imagem']);
        }

        $stmt = $conexao->prepare("DELETE FROM banners WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $response['success'] = true;
        $response['message'] = 'Banner excluído com sucesso!';
    } catch (PDOException $e) {
        $response['message'] = 'Erro ao excluir banner: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Erro: Nenhum ID fornecido para exclusão.';
}

echo json_encode($response);
exit;
?>
