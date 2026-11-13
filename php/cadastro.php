<?php

// Acesso direto (GET) ao script: volta para o formulário
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../html/cadastro.html');
    exit;
}

require_once 'conexao.php';

$erros = [];

// ---------- Leitura dos dados ----------
$nome      = trim($_POST['nome'] ?? '');
$data      = trim($_POST['data'] ?? '');
$cpfBruto  = trim($_POST['cpf'] ?? '');
$telefone  = trim($_POST['telefone'] ?? '');
$email     = trim($_POST['email'] ?? '');
$username  = trim($_POST['username'] ?? '');
$senha     = $_POST['senha'] ?? '';           
$confirmar = $_POST['confirmar_senha'] ?? '';

// ---------- Validações ----------

// Nome
if ($nome === '') {
    $erros[] = "O campo nome é obrigatório.";
} elseif (mb_strlen($nome) > 120) {
    $erros[] = "O nome deve ter no máximo 120 caracteres.";
}

// Data de nascimento
if ($data === '') {
    $erros[] = "O campo data de nascimento é obrigatório.";
} else {
    $dt = DateTime::createFromFormat('Y-m-d', $data);
    if (!$dt || $dt->format('Y-m-d') !== $data) {
        $erros[] = "Data de nascimento inválida.";
    } elseif ($data > date('Y-m-d')) {
        $erros[] = "A data de nascimento não pode ser maior que a data atual.";
    }
}

// CPF (guarda somente os dígitos)
$cpf = preg_replace('/\D/', '', $cpfBruto);
if ($cpfBruto === '') {
    $erros[] = "O campo CPF é obrigatório.";
} elseif (strlen($cpf) !== 11) {
    $erros[] = "O CPF deve conter 11 dígitos.";
}

// Telefone
if ($telefone === '') {
    $erros[] = "O campo telefone é obrigatório.";
} elseif (mb_strlen($telefone) > 20) {
    $erros[] = "O telefone deve ter no máximo 20 caracteres.";
}

// E-mail
if ($email === '') {
    $erros[] = "O campo e-mail é obrigatório.";
} elseif (mb_strlen($email) > 120 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    $erros[] = "E-mail inválido.";
}

// Username
if ($username === '') {
    $erros[] = "O campo username é obrigatório.";
} elseif (!preg_match('/^\w{3,30}$/', $username)) {
    $erros[] = "O username deve ter de 3 a 30 caracteres, usando apenas letras, números e _.";
}

// Senha
if ($senha === '') {
    $erros[] = "O campo senha é obrigatório.";
} elseif (strlen($senha) < 8) {
    $erros[] = "A senha deve ter no mínimo 8 caracteres.";
} elseif ($senha !== $confirmar) {
    $erros[] = "A confirmação de senha não confere.";
}

// ---------- Inserção (somente se não houve erros) ----------
if (empty($erros)) {
    try {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO usuarios
            (nome_completo, data_nascimento, cpf, telefone, email, username, senha)
            VALUES (?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([$nome, $data, $cpf, $telefone, $email, $username, $senha_hash]);

        // Sucesso: vai para a tela de login
        header('Location: ../index.html?cadastro=ok');
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            // Violação de chave única: username, CPF ou e-mail repetido
            $erros[] = "Usuário, CPF ou e-mail já cadastrado.";
        } else {
            error_log("Erro ao inserir usuario: " . $e->getMessage());
            $erros[] = "Erro ao realizar o cadastro. Tente novamente mais tarde.";
        }
    }
}

// ---------- Se chegou aqui, houve erro: exibe a lista ----------
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro no cadastro</title>
    <link rel="stylesheet" href="../css/variaveis.css">
    <link rel="stylesheet" href="../css/cadastro.css">
</head>
<body>
    <div class="cadastro">
        <h1>Não foi possível concluir o cadastro</h1>
        <ul>
<?php foreach ($erros as $erro): ?>
            <li><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></li>
<?php endforeach; ?>
        </ul>
        <div class="containerBotao">
            <a href="../pages/cadastro.html" class="botao">Voltar ao formulário</a>
        </div>
    </div>
</body>
</html>