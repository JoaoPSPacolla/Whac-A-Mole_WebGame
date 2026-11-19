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
        error_log('Erro ao carregar edição de dados: ' . $ex->getMessage());
        $erroBanco = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar informações</title>
    <link rel="stylesheet" href="../css/variaveis.css">
    <link rel="stylesheet" href="../css/conta.css">
    <link rel="stylesheet" href="../css/cadastro.css">
</head>
<body>
    <div class="conta">
        <?php if ($erroBanco || !$usuario): ?>
            <p class="erroGeral">Não foi possível carregar seus dados para edição.</p>
            <div class="containerBotao">
                <a href="conta.php" class="botao">Voltar</a>
            </div>
        <?php else: ?>
            <form id="formularioEdicao" method="POST" action="../php/editar_dados.php" novalidate>
                <div class="erroGeral" id="erroGeral" role="alert" hidden></div>
                <div class="sucessoGeral" id="sucessoGeral" role="status" hidden></div>

                <div class="campo">
                    <label for="nome">Nome Completo: </label>
                    <input type="text" id="nome" name="nome" value="<?= e($usuario['nome_completo']) ?>" maxlength="120" required>
                    <small class="erroCampo" id="erro-nome"></small>
                </div>

                <div class="campo">
                    <label class="aviso" for="data">Impossível alterar data de nascimento</label>
                    <input type="date" id="data" name="data" value="<?= e($usuario['data_nascimento']) ?>" readonly>
                </div>

                <div class="campo">
                    <label class="aviso" for="cpf">Impossível alterar seu CPF!</label>
                    <input type="text" id="cpf" name="cpf" value="<?= e($usuario['cpf']) ?>" readonly>
                </div>

                <div class="campo">
                    <label for="telefone">Telefone: </label>
                    <input type="tel" id="telefone" name="telefone" value="<?= e($usuario['telefone']) ?>" maxlength="15" required>
                    <small class="erroCampo" id="erro-telefone"></small>
                </div>

                <div class="campo">
                    <label for="email">E-mail: </label>
                    <input type="email" id="email" name="email" value="<?= e($usuario['email']) ?>" maxlength="120" required>
                    <small class="erroCampo" id="erro-email"></small>
                </div>

                <div class="campo">
                    <label class="aviso" for="username">Impossível alterar seu username!</label>
                    <input type="text" id="username" name="username" value="<?= e($usuario['username']) ?>" readonly>
                </div>

                <div class="containerBotao">
                    <button type="submit" class="botao">Salvar alterações</button>
                </div>

                <div class="containerBotao">
                    <a href="conta.php" class="botao">Voltar</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script src="../js/edicaoCampos.js"></script>
</body>
</html>