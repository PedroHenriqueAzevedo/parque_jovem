<?php
include(__DIR__ . '/../../../conexao/conexao.php');

function buscarArquivosSabatina() {
    global $conexao;
    try {
        $stmt = $conexao->prepare("SELECT * FROM escola_sabatina ORDER BY id DESC");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as &$arquivo) {
            $arquivo['arquivo'] = 'uploads/' . $arquivo['arquivo'];
        }

        return $result;
    } catch (PDOException $e) {
        die("Erro ao buscar arquivos da escola sabatina: " . $e->getMessage());
    }
}

function buscarArquivoSabatinaPorId($id) {
    global $conexao;
    try {
        $stmt = $conexao->prepare("SELECT * FROM escola_sabatina WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $arquivo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($arquivo) {
            $arquivo['arquivo'] = 'uploads/' . $arquivo['arquivo'];
            return $arquivo;
        } else {
            return null;
        }
    } catch (PDOException $e) {
        die("Erro ao buscar arquivo da escola sabatina por ID: " . $e->getMessage());
    }
}
?>
