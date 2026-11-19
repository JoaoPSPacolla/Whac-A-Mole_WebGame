<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../php/conexao.php';

function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$usuario = null;
$erroBanco = !isset($conn);

if (!$erroBanco) {
    try {
        $stmt = $conn->prepare(
            "SELECT nome_completo, data_nascimento, cpf, telefone, email, username
               FROM usuarios
              WHERE id = ?"
        );
        $stmt->execute([(int) $_SESSION['usuario_id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        error_log('Erro ao carregar dados da conta: ' . $ex->getMessage());
        $erroBanco = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta</title>
    <link rel="stylesheet" href="../css/variaveis.css">
    <link rel="stylesheet" href="../css/conta.css">
</head>
<body>
    <div class="conta">
        <?php if ($erroBanco || !$usuario): ?>
            <p class="erroGeral">Não foi possível carregar os dados da conta no momento.</p>
        <?php else: ?>
            <form>
                <label>Nome Completo: </label><br>
                <input type="text" name="nome" value="<?= e($usuario['nome_completo']) ?>" readonly><br><br>

                <label>Data de nascimento: </label><br>
                <input type="date" name="data" value="<?= e($usuario['data_nascimento']) ?>" readonly><br><br>

                <label>CPF: </label><br>
                <input type="text" name="cpf" value="<?= e($usuario['cpf']) ?>" readonly><br><br>

                <label>Telefone: </label><br>
                <input type="tel" name="telefone" value="<?= e($usuario['telefone']) ?>" readonly><br><br>

                <label>E-mail: </label><br>
                <input type="email" name="email" value="<?= e($usuario['email']) ?>" readonly><br><br>

                <label>Username: </label><br>
                <input type="text" name="username" value="<?= e($usuario['username']) ?>" readonly><br><br>

                <div>
                    <a href="alteraSenha.php" class="botao">Alterar senha</a>
                </div>

                <div class="containerBotao">
                    <a href="edicaoCampos.php" class="botao">Alterar Informações</a>
                </div>

                <div class="containerBotao">
                    <a href="home.php" class="botao">Voltar para a Home</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>