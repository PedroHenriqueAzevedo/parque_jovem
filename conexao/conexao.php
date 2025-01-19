<?php
// Configurações do banco de dados
$host = 'localhost'; // Altere se necessário
$dbname = 'parque_joven';
$username = 'root'; // Altere se necessário
$password = ''; // Altere se necessário

try {
    // Criar conexão com PDO
    $conexao = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Exibir erro de conexão
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
