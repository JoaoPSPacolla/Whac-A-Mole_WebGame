<?php
    session_start();

    if(!isset($_SESSION['usuario_id'])){
        header('Location: ../index.php');
        exit;
    }
    
?>
<!DOCTYPE html>
<html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Página inicial</title>
        <link rel="stylesheet" href="../css/variaveis.css">
        <link rel="stylesheet" href="../css/home.css">
    </head>

    <body>  
        <main>
            <header>
                <img src="../assets/imgs/logo.png" alt="Logo do jogo">
                <p>Escolha para onde quer ir.</p>
            </header>

            <nav class="menu">

                <a class="paginas" id="jogar" href="jogo.html">
                    <span class="titulo_PG">Jogar</span>
                </a>
                <a class="paginas" id="ranking" href="ranking.html">
                    <span class="titulo_PG">Ranking global</span>
                </a>

                <a class="paginas" id="historico" href="historico.html">
                    <span class="titulo_PG">Histórico</span>
                </a>

                <a class="paginas" id="conta" href="conta.php">
                    <span class="titulo_PG">Minha conta</span>
                </a>

                <a class="paginas" id="sair" href="../php/logout.php">
                    <span class="titulo_PG">Sair</span>
                </a>
            </nav>
        </main>
    </body>
</html>
