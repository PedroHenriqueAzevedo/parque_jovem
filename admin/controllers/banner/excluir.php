<?php
require_once '../conexao/conexao.php';
require_once '../admin/controllers/banner/buscar.php';

function excluirBanner($id) {
    global $conexao;

    try {
        $banner = buscarBannerPorId($id);
        if ($banner && file_exists($banner['imagem'])) {
            unlink($banner['imagem']);
        }

        $stmt = $conexao->prepare("DELETE FROM banners WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return true;
    } catch (PDOException $e) {
        die("Erro ao excluir banner: " . $e->getMessage());
    }
}
?>
