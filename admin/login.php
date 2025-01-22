<?php
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin/index.php');
    exit;
}
?>


<div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="card p-4" style="width: 100%; max-width: 400px;">
        <h2 class="text-center mb-4">Login - Admin</h2>

        <!-- Mensagem de erro -->
        <?php if (isset($_GET['erro'])): ?>
            <div class="alert alert-danger text-center">
                <?= htmlspecialchars($_GET['erro']) ?>
            </div>
        <?php endif; ?>

        <form action="verifica_login.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">E-mail:</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha:</label>
                <input type="password" name="senha" id="senha" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</div>

<?php include '../cabecalho/footer.php'; ?> <!-- Incluindo o footer -->
