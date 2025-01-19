<?php
include(__DIR__ . '/../../../conexao/conexao.php');


function buscarBanners() {
    global $conexao;

    try {
        $stmt = $conexao->prepare("SELECT * FROM banners ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erro ao buscar banners: " . $e->getMessage());
    }
}

function buscarBannerPorId($id) {
    global $conexao;

    try {
        $stmt = $conexao->prepare("SELECT * FROM banners WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erro ao buscar banner: " . $e->getMessage());
    }
}
?>
