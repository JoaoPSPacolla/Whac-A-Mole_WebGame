<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../php/conexao.php';

function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

$modalidades = ['classica' => 'Clássica', 'explosiva' => 'Explosiva'];
$statusRotulos = ['finalizada' => 'Finalizada', 'desistencia' => 'Desistência'];

$usuarioId = (int) $_SESSION['usuario_id'];

$partidas = [];
$melhorId = null;
$maxPontuacao = null;
$maxNivel = null;
$erroBanco = !isset($conn);

if (!$erroBanco) {
    try {
        // Todas as partidas do usuário logado, da mais recente para a mais antiga.
        $stmt = $conn->prepare(
            "SELECT p.id, u.nome_completo, p.linhas, p.colunas, p.modalidade,
                    p.pontuacao, p.toupeiras, p.nivel, p.status, p.data_hora
               FROM partidas p
               JOIN usuarios u ON u.id = p.usuario_id
              WHERE p.usuario_id = ?
              ORDER BY p.data_hora DESC, p.id DESC
              LIMIT 100"
        );
        $stmt->execute([$usuarioId]);
        $partidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Os cartões consideram só partidas finalizadas, igual ao ranking.
        $stmt = $conn->prepare(
            "SELECT MAX(pontuacao) AS max_pontuacao, MAX(nivel) AS max_nivel
               FROM partidas
              WHERE usuario_id = ? AND status = 'finalizada'"
        );
        $stmt->execute([$usuarioId]);
        $resumo = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxPontuacao = $resumo['max_pontuacao'];
        $maxNivel = $resumo['max_nivel'];

        // Melhor desempenho: mesmo critério do ranking (nível, depois pontuação).
        $stmt = $conn->prepare(
            "SELECT id
               FROM partidas
              WHERE usuario_id = ? AND status = 'finalizada'
              ORDER BY nivel DESC, pontuacao DESC, data_hora ASC
              LIMIT 1"
        );
        $stmt->execute([$usuarioId]);
        $melhorId = $stmt->fetchColumn();
    } catch (PDOException $ex) {
        error_log('Erro ao carregar histórico: ' . $ex->getMessage());
        $erroBanco = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Partidas</title>
    <link rel="stylesheet" href="../css/variaveis.css">
    <link rel="stylesheet" href="../css/historico.css">
</head>

<body>
    <main class="paginaHistorico">
        <header class="topbar">
            <div class="marca">
                <span>Acerte a toupeira!</span>
            </div>

            <nav class="linksNavegacao">
                <a href="home.php">Home</a>
                <a href="jogo.html">Jogar</a>
                <a href="conta.php">Perfil</a>
                <a href="ranking.php">Ranking</a>
                <a href="../php/logout.php">Sair</a>
            </nav>
        </header>

                <section class="painelHistorico">
            <div class="cabecalhoPainel">
                <p class="rótulo">Tabela do Usuário</p>
                <h1 id="historico-title">Histórico</h1>
            </div>

            <div class="gradeEstatisticas">
                <div class="cartaoEstatistica">
                    <span class="rotuloCartao">Melhor desempenho</span>
                    <strong><?= $melhorId ? 'Partida n° ' . str_pad((string) $melhorId, 6, '0', STR_PAD_LEFT) : '-' ?></strong>
                </div>
                <div class="cartaoEstatistica">
                    <span class="rotuloCartao">Pontuação máxima</span>
                    <strong><?= $maxPontuacao !== null ? number_format((int) $maxPontuacao, 0, ',', '.') : '-' ?></strong>
                </div>
                <div class="cartaoEstatistica">
                    <span class="rotuloCartao">Nível mais alto</span>
                    <strong><?= $maxNivel !== null ? (int) $maxNivel : '-' ?></strong>
                </div>
            </div>

            <?php if ($erroBanco): ?>
                <p class="mensagemHistorico">Não foi possível carregar o histórico agora. Tente novamente mais tarde.</p>
            <?php elseif (empty($partidas)): ?>
                <p class="mensagemHistorico">Você ainda não jogou nenhuma partida.</p>
            <?php else: ?>
            <div class="areaTabela">
                <table>
                    <thead>
                        <tr>
                            <th>Num. Partida</th>
                            <th>Nome do Jogador</th>
                            <th>Dimensão</th>
                            <th>Modalidade</th>
                            <th>Pontuação</th>
                            <th>Num. Toupeiras</th>
                            <th>Nível</th>
                            <th>Status</th>
                            <th>Data/Hora</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($partidas as $partida): ?>
                        <tr>
                            <td><?= str_pad((string) $partida['id'], 6, '0', STR_PAD_LEFT) ?></td>
                            <td><?= e($partida['nome_completo']) ?></td>
                            <td><?= (int) $partida['linhas'] ?>x<?= (int) $partida['colunas'] ?></td>
                            <td><?= e($modalidades[$partida['modalidade']] ?? $partida['modalidade']) ?></td>
                            <td><?= number_format((int) $partida['pontuacao'], 0, ',', '.') ?></td>
                            <td><?= number_format((int) $partida['toupeiras'], 0, ',', '.') ?></td>
                            <td><?= (int) $partida['nivel'] ?></td>
                            <td><?= e($statusRotulos[$partida['status']] ?? $partida['status']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($partida['data_hora'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>