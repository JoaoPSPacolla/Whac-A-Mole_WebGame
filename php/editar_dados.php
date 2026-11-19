<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/edicaoCampos.php');
    exit;
}

$ehAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

function responderJson(int $codigo, array $corpo): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($corpo);
    exit;
}

if (!isset($_SESSION['usuario_id'])) {
    if ($ehAjax) {
        responderJson(401, ['ok' => false, 'erros' => ['geral' => 'Sessão expirada. Faça login novamente.']]);
    }
    header('Location: ../index.php');
    exit;
}

ob_start();
require_once 'conexao.php';
ob_end_clean();

$usuarioId = (int) $_SESSION['usuario_id'];
$nome      = trim($_POST['nome'] ?? '');
$telefone  = trim($_POST['telefone'] ?? '');
$email     = trim($_POST['email'] ?? '');
$erros     = [];

if ($nome === '') {
    $erros['nome'] = "O campo nome é obrigatório.";
} elseif (mb_strlen($nome) > 120) {
    $erros['nome'] = "O nome deve ter no máximo 120 caracteres.";
}

if ($telefone === '') {
    $erros['telefone'] = "O campo telefone é obrigatório.";
} elseif (mb_strlen($telefone) > 15) {
    $erros['telefone'] = "O telefone deve ter no máximo 15 caracteres, exemplo: (11) 98765-4321.";
}

if ($email === '') {
    $erros['email'] = "O campo e-mail é obrigatório.";
} elseif (mb_strlen($email) > 120 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    $erros['email'] = "E-mail inválido.";
}

$atualizado = false;

if (empty($erros)) {
    if (!isset($conn)) {
        $erros['geral'] = "Erro ao conectar ao banco de dados. Tente novamente mais tarde.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $usuarioId]);
            if ($stmt->fetch()) {
                $erros['email'] = "Este e-mail já está em uso por outra conta.";
            } else {
                $stmt = $conn->prepare(
                    "UPDATE usuarios
                        SET nome_completo = ?, telefone = ?, email = ?
                      WHERE id = ?"
                );
                $stmt->execute([$nome, $telefone, $email, $usuarioId]);
                $atualizado = true;
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $erros['email'] = "Este e-mail já está em uso.";
            } else {
                error_log("Erro ao atualizar dados do usuario: " . $e->getMessage());
                $erros['geral'] = "Erro ao salvar alterações. Tente novamente mais tarde.";
            }
        }
    }
}

if ($atualizado) {
    $mensagemSucesso = "Informações atualizadas com sucesso!";
    if ($ehAjax) {
        responderJson(200, ['ok' => true, 'mensagem' => $mensagemSucesso]);
    }
    header('Location: ../pages/conta.php');
    exit;
}

if ($ehAjax) {
    responderJson(422, ['ok' => false, 'erros' => $erros]);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Erro ao atualizar</title>
    <link rel="stylesheet" href="../css/variaveis.css">
    <link rel="stylesheet" href="../css/conta.css">
</head>
<body>
    <div class="conta">
        <h1>Não foi possível salvar as alterações</h1>
        <ul>
            <?php foreach (array_values($erros) as $erro): ?>
                <li><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
        <div class="containerBotao">
            <a href="../pages/edicaoCampos.php" class="botao">Voltar</a>
        </div>
    </div>
</body>
</html>