<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/alteraSenha.php');
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

$usuarioId      = (int) $_SESSION['usuario_id'];
$senhaAtual     = $_POST['senha_atual'] ?? '';
$novaSenha      = $_POST['nova_senha'] ?? '';
$confirmarSenha = $_POST['confirmar_senha'] ?? '';
$erros          = [];

if ($senhaAtual === '') {
    $erros['senha_atual'] = "Informe sua senha atual.";
}

if ($novaSenha === '') {
    $erros['nova_senha'] = "Informe a nova senha.";
} elseif (strlen($novaSenha) < 8) {
    $erros['nova_senha'] = "A nova senha deve ter no mínimo 8 caracteres.";
} elseif ($senhaAtual !== '' && $novaSenha === $senhaAtual) {
    $erros['nova_senha'] = "A nova senha não pode ser igual à senha atual.";
}

if ($confirmarSenha === '') {
    $erros['confirmar_senha'] = "Confirme a nova senha.";
} elseif ($novaSenha !== $confirmarSenha) {
    $erros['confirmar_senha'] = "A confirmação de senha não confere.";
}

$alterada = false;

if (empty($erros)) {
    if (!isset($conn)) {
        $erros['geral'] = "Erro ao conectar ao banco de dados. Tente novamente mais tarde.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE id = ?");
            $stmt->execute([$usuarioId]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario || !password_verify($senhaAtual, $usuario['senha'])) {
                $erros['senha_atual'] = "Senha atual incorreta.";
            } elseif (password_verify($novaSenha, $usuario['senha'])) {
                $erros['nova_senha'] = "A nova senha não pode ser igual à senha atual.";
            } else {
                $novoHash = password_hash($novaSenha, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                $stmt->execute([$novoHash, $usuarioId]);
                $alterada = true;
            }
        } catch (PDOException $e) {
            error_log("Erro ao alterar senha: " . $e->getMessage());
            $erros['geral'] = "Erro ao alterar a senha. Tente novamente mais tarde.";
        }
    }
}

if ($alterada) {
    $mensagemSucesso = "Senha alterada com sucesso!";
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
    <title>Erro ao alterar senha</title>
    <link rel="stylesheet" href="../css/variaveis.css">
    <link rel="stylesheet" href="../css/conta.css">
</head>
<body>
    <div class="conta">
        <h1>Não foi possível alterar a senha</h1>
        <ul>
            <?php foreach (array_values($erros) as $erro): ?>
                <li><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
        <div class="containerBotao">
            <a href="../pages/alteraSenha.php" class="botao">Voltar</a>
        </div>
    </div>
</body>
</html>