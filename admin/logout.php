<?php
session_start();

// Destroi todas as sessões ativas
session_unset();
session_destroy();

// Redireciona para a página de login
header('Location: login.php');
exit;
?>
