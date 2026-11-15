<?php
    session_start();
    $errosLogin = $_SESSION['erros_login'] ?? [];
    unset($_SESSION['erros_login']);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/variaveis.css">
    <link rel="stylesheet" href="css/login.css">
    <script src="js/login.js"></script>
</head>

<body>
     <main class="caixaLogin">   
        <form class="caixaTexto" method="POST" action="php/login.php">
            <h1>Login</h1>

            <!-- Se houver erro na tentativa de login, as mensagens aparecem dentro da caixa -->
            <?php if (!empty($errosLogin)): ?>
            <ul class="erros" role="alert">
                <?php foreach ($errosLogin as $erro): ?>
                    <li><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <div class="campoInput">
                <input type="text" placeholder="Usuário" name="usuario" required>
                <img src="assets/icones/user.png" alt="Ícone usuário" class="iconeInput">
            </div>

            <div class="campoInput">
                <input type="password" placeholder="Senha" name="senha" required>
                <img src="assets/icones/cadeado-trancado.png" alt="Ícone senha" class="iconeInput">
            </div>

            <div class="opcoesLogin">
                <label id="lembrarLogin">
                    <input type="checkbox" name="lembrar"> Lembrar de mim
                </label>
                <p id="esqueceuSenha" onclick="mensagem()">Esqueci minha senha</p>
            </div>

            <button type="submit">Entrar</button>
            <a href="pages/cadastro.html" id="semConta">Não tenho conta</a>
        </form>
    </main>
</body>

</html>