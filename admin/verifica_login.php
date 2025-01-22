<?php
session_start();
require_once '../admin/controllers/loginController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    // Autenticar o usuário
    $resultado = autenticarUsuario($email, $senha);

    if ($resultado['sucesso']) {
        // Iniciar sessão para o administrador
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $resultado['usuario']['email'];

        // Redirecionar para o painel administrativo
        header('Location: ../admin/index.php');
        exit;
    } else {
        // Armazenar mensagem de erro na sessão
        $_SESSION['erro_login'] = $resultado['erro'];

        // Redirecionar para a página de login sem parâmetros na URL
        header('Location: ../admin/login.php');
        exit;
    }
}
?>
