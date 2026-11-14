<?php

session_start();

//esvazia os dados da sessão
$_SESSION = [];

//apaga os cookies da sessão no navegador
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

//destroi a sessão do usuário no servidor
session_destroy();

header('Location: ../index.php');
exit;