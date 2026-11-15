<?php
// Recebe (via POST/JSON) os dados de uma partida terminada e grava na tabela partidas.
// O usuário vem SEMPRE da sessão, nunca do que o navegador envia.

session_start();
header('Content-Type: application/json; charset=utf-8');

function responder(int $codigo, array $corpo): void
{
    http_response_code($codigo);
    echo json_encode($corpo);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['ok' => false, 'erro' => 'Método não permitido.']);
}

if (!isset($_SESSION['usuario_id'])) {
    responder(401, ['ok' => false, 'erro' => 'Usuário não autenticado.']);
}

// conexao.php dá echo em caso de falha; o buffer evita que esse texto misture com o JSON.
ob_start();
require_once 'conexao.php';
ob_end_clean();

if (!isset($conn)) {
    responder(500, ['ok' => false, 'erro' => 'Erro ao conectar ao banco de dados.']);
}

$dados = json_decode(file_get_contents('php://input'), true);
if (!is_array($dados)) {
    responder(400, ['ok' => false, 'erro' => 'Dados inválidos.']);
}

// Validação (mesmas regras do banco: tabuleiro de 4 a 64 buracos, ENUMs etc.)
$linhas     = filter_var($dados['linhas'] ?? null, FILTER_VALIDATE_INT);
$colunas    = filter_var($dados['colunas'] ?? null, FILTER_VALIDATE_INT);
$pontuacao  = filter_var($dados['pontuacao'] ?? null, FILTER_VALIDATE_INT);
$toupeiras  = filter_var($dados['toupeiras'] ?? null, FILTER_VALIDATE_INT);
$nivel      = filter_var($dados['nivel'] ?? null, FILTER_VALIDATE_INT);
$modalidade = $dados['modalidade'] ?? '';
$status     = $dados['status'] ?? '';

$erros = [];

if ($linhas === false || $colunas === false || $linhas < 2 || $linhas > 8 || $colunas < 2 || $colunas > 8) {
    $erros[] = 'Tamanho de tabuleiro inválido.';
}
if (!in_array($modalidade, ['classica', 'explosiva'], true)) {
    $erros[] = 'Modalidade inválida.';
}
if ($pontuacao === false || abs($pontuacao) > 1000000) {
    $erros[] = 'Pontuação inválida.';
}
if ($toupeiras === false || $toupeiras < 0 || $toupeiras > 1000000) {
    $erros[] = 'Quantidade de toupeiras inválida.';
}
if ($nivel === false || $nivel < 1 || $nivel > 65535) {
    $erros[] = 'Nível inválido.';
}
if (!in_array($status, ['finalizada', 'desistencia'], true)) {
    $erros[] = 'Status inválido.';
}

if (!empty($erros)) {
    responder(422, ['ok' => false, 'erro' => implode(' ', $erros)]);
}

try {
    $stmt = $conn->prepare(
        "INSERT INTO partidas (usuario_id, linhas, colunas, modalidade, pontuacao, toupeiras, nivel, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $_SESSION['usuario_id'],
        $linhas,
        $colunas,
        $modalidade,
        $pontuacao,
        $toupeiras,
        $nivel,
        $status,
    ]);

    responder(201, ['ok' => true, 'id' => (int) $conn->lastInsertId()]);
} catch (PDOException $e) {
    error_log('Erro ao salvar partida: ' . $e->getMessage());
    responder(500, ['ok' => false, 'erro' => 'Erro ao salvar a partida.']);
}