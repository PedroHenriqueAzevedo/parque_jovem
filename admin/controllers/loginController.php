<?php
session_start();
require_once __DIR__ . '/../../conexao/conexao.php';

function autenticarUsuario($email, $senha) {
    global $conexao;

    try {
        $stmt = $conexao->prepare("SELECT * FROM admin WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            if ($admin['senha'] === $senha) { 
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_nome'] = $admin['nome']; 
                header('Location: admin/index.php');
                exit;
            } else {
                $_SESSION['erro_login'] = 'Senha incorreta.';
            }
        } else {
            $_SESSION['erro_login'] = 'E-mail não encontrado.';
        }
    } catch (PDOException $e) {
        $_SESSION['erro_login'] = 'Erro ao conectar ao banco.';
    }

    // Redireciona de volta ao login caso ocorra erro
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    autenticarUsuario($email, $senha);
}
?>
