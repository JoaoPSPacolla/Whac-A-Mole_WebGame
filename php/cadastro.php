<?php

// Acesso direto (GET) ao script: volta para o formulário
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/cadastro.html');
    exit;
}

// O formulário (js/cadastro.js) envia este cabeçalho. Nesse caso a resposta é JSON e a
// página do cadastro não é recarregada. Sem JavaScript, vale o fluxo antigo (página de erro).
$ehAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

function responderJson(int $codigo, array $corpo): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($corpo);
    exit;
}

// conexao.php dá echo em caso de falha; o buffer evita que esse texto misture com o JSON.
ob_start();
require_once 'conexao.php';
ob_end_clean();

// Os erros são indexados pelo name do campo do formulário; "geral" é para erros sem campo.
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
    $erros['nome'] = "O campo nome é obrigatório.";
} elseif (mb_strlen($nome) > 120) {
    $erros['nome'] = "O nome deve ter no máximo 120 caracteres.";
}

// Data de nascimento
if ($data === '') {
    $erros['data'] = "O campo data de nascimento é obrigatório.";
} else {
    $dt = DateTime::createFromFormat('Y-m-d', $data);
    if (!$dt || $dt->format('Y-m-d') !== $data) {
        $erros['data'] = "Data de nascimento inválida.";
    } elseif ($data > date('Y-m-d')) {
        $erros['data'] = "A data de nascimento não pode ser maior que a data atual.";
    }
}

// CPF (guarda somente os dígitos)
$cpf = preg_replace('/\D/', '', $cpfBruto); //retira todos os caracteres que não forem numéricos
if ($cpfBruto === '') {
    $erros['cpf'] = "O campo CPF é obrigatório.";
} elseif (strlen($cpf) !== 11) {
    $erros['cpf'] = "O CPF deve conter 11 dígitos.";
}

// Telefone
if ($telefone === '') {
    $erros['telefone'] = "O campo telefone é obrigatório.";
} elseif (mb_strlen($telefone) > 15) {
    $erros['telefone'] = "O telefone deve ter no máximo 15 caracteres, exemplo: (11) 98765-4321.";
}

// E-mail
if ($email === '') {
    $erros['email'] = "O campo e-mail é obrigatório.";
} elseif (mb_strlen($email) > 120 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    $erros['email'] = "E-mail inválido.";
}

// Username
if ($username === '') {
    $erros['username'] = "O campo username é obrigatório.";
} elseif (!preg_match('/^\w{3,30}$/', $username)) {
    $erros['username'] = "O username deve ter de 3 a 30 caracteres, usando apenas letras, números e _.";
}

// Senha
if ($senha === '') {
    $erros['senha'] = "O campo senha é obrigatório.";
} elseif (strlen($senha) < 8) {
    $erros['senha'] = "A senha deve ter no mínimo 8 caracteres.";
} elseif ($senha !== $confirmar) {
    $erros['confirmar_senha'] = "A confirmação de senha não confere.";
}

// ---------- Inserção (somente se não houve erros) ----------
$cadastrado = false;

if (empty($erros)) {
    if (!isset($conn)) {
        $erros['geral'] = "Erro ao conectar ao banco de dados. Tente novamente mais tarde.";
    } else {
        try {
            // Verifica antes se username, CPF ou e-mail já existem, para avisar no campo certo.
            $stmt = $conn->prepare("SELECT username, cpf, email FROM usuarios
                                    WHERE username = ? OR cpf = ? OR email = ?");
            $stmt->execute([$username, $cpf, $email]);

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $existente) {
                if (strcasecmp($existente['username'], $username) === 0) {
                    $erros['username'] = "Este username já está em uso.";
                }
                if ($existente['cpf'] === $cpf) {
                    $erros['cpf'] = "Este CPF já está cadastrado.";
                }
                if (strcasecmp($existente['email'], $email) === 0) {
                    $erros['email'] = "Este e-mail já está cadastrado.";
                }
            }

            if (empty($erros)) {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO usuarios
                    (nome_completo, data_nascimento, cpf, telefone, email, username, senha)
                    VALUES (?, ?, ?, ?, ?, ?, ?)");

                $stmt->execute([$nome, $data, $cpf, $telefone, $email, $username, $senha_hash]);
                $cadastrado = true;
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                // Violação de chave única (caso dois cadastros iguais cheguem ao mesmo tempo)
                $erros['geral'] = "Usuário, CPF ou e-mail já cadastrado.";
            } else {
                error_log("Erro ao inserir usuario: " . $e->getMessage()); // log para análise do erro por parte do dev
                $erros['geral'] = "Erro ao realizar o cadastro. Tente novamente mais tarde.";
            }
        }
    }
}

// ---------- Resposta ----------
if ($cadastrado) {
    if ($ehAjax) {
        responderJson(201, ['ok' => true, 'redirect' => '../index.php']);
    }
    // Sucesso: vai para a tela de login
    header('Location: ../index.php');
    exit;
}

if ($ehAjax) {
    responderJson(422, ['ok' => false, 'erros' => $erros]);
}

// Sem JavaScript: exibe a lista de erros em uma página própria.
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
            <?php foreach (array_values($erros) as $erro): ?>
                <li><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
        <div class="containerBotao">
            <a href="../pages/cadastro.html" class="botao">Voltar ao formulário</a>
        </div>
    </div>
</body>
</html>