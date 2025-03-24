<?php 
include '../cabecalho/header.php'; 
include '../conexao/conexao.php';

$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $tipo_cadastro = $_POST['tipo_cadastro'];
    $adventista = $_POST['adventista'];
    $igreja = $adventista === 'Sim' ? $_POST['igreja'] : null;

    $stmt = $conexao->prepare("INSERT INTO cadastros_jovens (nome, telefone, tipo_cadastro, adventista, igreja) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $telefone, $tipo_cadastro, $adventista, $igreja]);

    $sucesso = true;
}
?>

<div class="container mt-5 mb-5">
    <div class="card shadow"> <!-- card padrão, sem bg-dark -->
        <div class="card-body">
            <!-- Botão de voltar com cor do admin -->
            <a href="../index.php" class="btn btn-secondary mb-3">
    <i class="bi bi-arrow-left"></i> Voltar
</a>



            <h3 class="card-title mb-4">Cadastro de Jovens</h3>

            <?php if ($sucesso): ?>
                <div class="alert alert-success">
                    Cadastro enviado com sucesso!<br>
                    Os coordenadores dos jovens entrarão em contato com você via WhatsApp para mais informações.
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nome completo:</label>
                    <input type="text" class="form-control" name="nome" placeholder="Ex: João da Silva" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Telefone (com DDD):</label>
                    <input type="text" class="form-control" name="telefone" id="telefone" placeholder="(00) 00000-0000" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">O que você deseja?</label>
                    <select class="form-select" name="tipo_cadastro" required>
                        <option value="">Selecione</option>
                        <option value="Tenho interesse em me batizar">Tenho interesse em me batizar</option>
                        <option value="Quero oração">Quero oração</option>
                        <option value="Quero participar da classe biblíca">Quero participar da classe bíblica</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Você é da Igreja Adventista?</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="adventista" value="Sim" required onclick="document.getElementById('campo_igreja').style.display='block'">
                        <label class="form-check-label">Sim</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="adventista" value="Não" required onclick="document.getElementById('campo_igreja').style.display='none'">
                        <label class="form-check-label">Não</label>
                    </div>
                </div>

                <div class="mb-3" id="campo_igreja" style="display:none;">
                    <label class="form-label">Qual igreja?</label>
                    <input type="text" class="form-control" name="igreja" placeholder="Ex: IASD Parque São Domingos">
                </div>

                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>
        </div>
    </div>
</div>

<script>
// Máscara de telefone (99) 99999-9999
document.getElementById('telefone').addEventListener('input', function (e) {
    let input = e.target;
    input.value = formatarTelefone(input.value);
});

function formatarTelefone(valor) {
    valor = valor.replace(/\D/g, ''); // Remove tudo que não é número
    valor = valor.substring(0, 11); // Máximo 11 dígitos

    if (valor.length <= 10) {
        return valor.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
    } else {
        return valor.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
    }
}
</script>

<?php include '../cabecalho/footer.php'; ?>
