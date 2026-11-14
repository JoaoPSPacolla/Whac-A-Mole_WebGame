<?php

session_start();

// Acesso direto (GET) ao script: volta para o formulário
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}


require_once 'conexao.php';

$erros = [];

// Recebimento dos dados via Post

$username = trim($_POST['usuario'] ?? '');
$senhaInformada = ($_POST['senha'] ?? '');


//Validação dos dados

if ($username === '')
    $erros[] = "O campo username é obrigatório.";

if ($senhaInformada === '') 
    $erros[] = "O campo senha é obrigatório.";


$usuario = false;

if (empty($erros)) {
    try {
        $stmt = $conn->prepare("SELECT id, senha FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        $usuario = $stmt->fetch();
    } catch (PDOException $e) {      
        error_log("Erro no login: " . $e->getMessage()); // log para análise do erro por parte do dev
        $erros[] = "Erro ao realizar o login. Tente novamente mais tarde.";       
    }
}


//Caso o usuário seja encontrado na base de dados E a senha estiver correta, há a criação da sessão.
//a função password_verify serve para comparar a senha fornecida com a senha do banco.
//session_regenerate_id() serve como segurança para o usuário ao trocar o ID que foi disponibilizado a ele quando iniciou a sessão na página
//Caso tudo dê certo, o usuário é realocado para a página Home
if($usuario && password_verify($senhaInformada, $usuario['senha'])){
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuario['id'];
    header('Location: ../pages/home.php');
    exit;
}

if(empty($erros))
    $erros[] = "Usuário ou senha inválidos.";

$_SESSION['erros_login'] = $erros;
header('Location: ../index.php');
exit;



