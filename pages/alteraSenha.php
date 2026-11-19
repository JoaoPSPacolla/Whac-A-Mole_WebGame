<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar senha</title>
    <link rel="stylesheet" href="../css/variaveis.css">
    <link rel="stylesheet" href="../css/conta.css">
    <link rel="stylesheet" href="../css/cadastro.css">
</head>
<body>
    <div class="conta">
        <form id="formularioSenha" method="POST" action="../php/alterar_senha.php" novalidate>
            <div class="erroGeral" id="erroGeral" role="alert" hidden></div>
            <div class="sucessoGeral" id="sucessoGeral" role="status" hidden></div>

            <div class="campo">
                <label for="senhaAtual">Senha atual: </label>
                <input type="password" id="senhaAtual" name="senha_atual" placeholder="Informe sua senha atual" required>
                <small class="erroCampo" id="erro-senha_atual"></small>
            </div>

            <div class="campo">
                <label for="novaSenha">Nova senha: </label>
                <input type="password" id="novaSenha" name="nova_senha" placeholder="Mínimo de 8 caracteres" required>
                <small class="erroCampo" id="erro-nova_senha"></small>
            </div>

            <div class="campo">
                <label for="confirmarSenha">Confirmar nova senha: </label>
                <input type="password" id="confirmarSenha" name="confirmar_senha" placeholder="Digite a nova senha novamente" required>
                <small class="erroCampo" id="erro-confirmar_senha"></small>
            </div>

            <div class="containerBotao">
                <button type="submit" class="botao">Salvar alterações</button>
            </div>

            <div class="containerBotao">
                <a href="conta.php" class="botao">Voltar</a>
            </div>
        </form>
    </div>

    <script src="../js/alteraSenha.js"></script>
</body>
</html>