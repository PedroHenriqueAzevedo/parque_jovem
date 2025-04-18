<?php
session_start();

header('Content-Type: application/json');

include(__DIR__ . '/../../../conexao/conexao.php');
require_once __DIR__ . '/buscar.php';

$response = ['success' => false, 'message' => 'Ocorreu um erro ao tentar excluir o banner.'];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Buscar os detalhes do banner pelo ID
        $banner = buscarBannerPorId($id);

        // Caminho correto da pasta de uploads sem "parque_joven"
        $uploadDir = __DIR__ . '/../../../uploads/';

        // Verificar se a imagem existe e excluir
        if ($banner && isset($banner['imagem'])) {
            $imagePath = $uploadDir . basename($banner['imagem']);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Excluir o registro do banco de dados
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
