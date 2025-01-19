<?php
require_once __DIR__ . '/../../conexao/conexao.php';

function autenticarUsuario($email, $senha) {
    global $conexao;

    try {
        // Buscar o usuário no banco de dados
        $stmt = $conexao->prepare("SELECT * FROM admin WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar se o usuário foi encontrado e validar a senha diretamente
        if ($admin) {
            if ($admin['senha'] === $senha) { // Comparação direta
                return ['sucesso' => true, 'usuario' => $admin];
            } else {
                return ['sucesso' => false, 'erro' => 'Senha incorreta.'];
            }
        } else {
            return ['sucesso' => false, 'erro' => 'E-mail não encontrado.'];
        }
    } catch (PDOException $e) {
        return ['sucesso' => false, 'erro' => 'Erro ao conectar ao banco: ' . $e->getMessage()];
    }
}
?>
