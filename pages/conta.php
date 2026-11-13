<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    // Usuário não está logado, redireciona para a página de login
    header('Location: index.html');
    exit;
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
</head>
<body>
    <div class="conta">
        <form>
            <label>Nome Completo: </label><br>
            <input type="text" name="nome" placeholder="Cleber da Cunha" readonly><br><br>

            <label>Data de nascimento: </label><br>
            <input type="date" name="data" readonly><br><br>

            <label>CPF: </label><br>
            <input type="text" name="cpf" placeholder="526574489562" readonly><br><br>

            <label>Telefone: </label><br>
            <input type="tel" name="telefone" placeholder="(19) 98115-0472" readonly><br><br>

            <label>E-mail: </label><br>
            <input type="email" name="email" placeholder="v195444@dac.unicamp.br" readonly><br><br>

            <label>Username: </label><br>
            <input type="text" name="username" placeholder="Clebão do Ping Pong" readonly><br><br>

            <div>
                <a href="alteraSenha.html" class="botao">Alterar senha</a>
            </div>

            <div class="containerBotao">
                <a href="edicaoCampos.html" class="botao">Alterar Informações</a>
            </div>

            <div class="containerBotao">
                <a href="home.html" class="botao">Voltar para a Home</a>
            </div>

        </form>

    </div>



</body>
</html>