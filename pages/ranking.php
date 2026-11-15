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

// Filtro opcional por modalidade: ranking.php?modalidade=classica
$filtro = $_GET['modalidade'] ?? '';
if (!isset($modalidades[$filtro])) {
    $filtro = '';
}

// Só partidas finalizadas entram no ranking (desistências ficam de fora).
$where = "p.status = 'finalizada'";
$params = [];
if ($filtro !== '') {
    $where .= " AND p.modalidade = ?";
    $params[] = $filtro;
}

$ranking = [];
$maxPontuacao = null;
$maxNivel = null;
$erroBanco = !isset($conn);

if (!$erroBanco) {
    try {
        // Ordem: maior nível, depois maior pontuação; no empate, quem chegou primeiro.
        $stmt = $conn->prepare(
            "SELECT u.username, u.nome_completo, p.linhas, p.colunas, p.modalidade,
                    p.pontuacao, p.nivel, p.data_hora
               FROM partidas p
               JOIN usuarios u ON u.id = p.usuario_id
              WHERE $where
              ORDER BY p.nivel DESC, p.pontuacao DESC, p.data_hora ASC
              LIMIT 10"
        );
        $stmt->execute($params);
        $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $conn->prepare(
            "SELECT MAX(p.pontuacao) AS max_pontuacao, MAX(p.nivel) AS max_nivel
               FROM partidas p
              WHERE $where"
        );
        $stmt->execute($params);
        $resumo = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxPontuacao = $resumo['max_pontuacao'];
        $maxNivel = $resumo['max_nivel'];
    } catch (PDOException $ex) {
        error_log('Erro ao carregar ranking: ' . $ex->getMessage());
        $erroBanco = true;
    }
}

$campeao = $ranking[0]['username'] ?? '-';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking Global</title>
    <link rel="stylesheet" href="../css/variaveis.css">
    <link rel="stylesheet" href="../css/ranking.css">
</head>

<body>
    <main class="paginaRanking">
        <header class="topbar">
            <div class="marca">
                <span>Acerte a toupeira!</span>
            </div>

            <nav class="linksNavegacao">
                <a href="home.php">Home</a>
                <a href="jogo.html">Jogar</a>
                <a href="conta.php">Perfil</a>
                <a href="historico.php">Histórico</a>
                <a href="../php/logout.php">Sair</a>
            </nav>
        </header>

                <section class="painelRanking">
            <div class="cabecalhoPainel">
                <p class="rótulo">Tabela geral</p>
                <h1 id="ranking-title">Ranking Global</h1>
            </div>

            <nav class="filtroRanking" aria-label="Filtrar por modalidade">
                <a href="ranking.php" class="<?= $filtro === '' ? 'ativo' : '' ?>">Todas</a>
                <?php foreach ($modalidades as $valor => $rotulo): ?>
                    <a href="ranking.php?modalidade=<?= e($valor) ?>" class="<?= $filtro === $valor ? 'ativo' : '' ?>"><?= e($rotulo) ?></a>
                <?php endforeach; ?>
            </nav>

            <div class="gradeEstatisticas">
                <article class="cartaoEstatistica">
                    <h2 class="sr-only">Campeão atual</h2>
                    <span class="rotuloCartao">Campeão atual</span>
                    <strong><?= e($campeao) ?></strong>
                </article>
                <article class="cartaoEstatistica">
                    <h2 class="sr-only">Pontuação máxima</h2>
                    <span class="rotuloCartao">Pontuação máxima</span>
                    <strong><?= $maxPontuacao !== null ? number_format((int) $maxPontuacao, 0, ',', '.') : '-' ?></strong>
                </article>
                <article class="cartaoEstatistica">
                    <h2 class="sr-only">Nível mais alto</h2>
                    <span class="rotuloCartao">Nível mais alto</span>
                    <strong><?= $maxNivel !== null ? (int) $maxNivel : '-' ?></strong>
                </article>
            </div>

            <?php if ($erroBanco): ?>
                <p class="mensagemRanking">Não foi possível carregar o ranking agora. Tente novamente mais tarde.</p>
            <?php elseif (empty($ranking)): ?>
                <p class="mensagemRanking">Ainda não há partidas finalizadas. Seja o primeiro a pontuar!</p>
            <?php else: ?>
            <div class="areaTabela">
                <table>
                    <thead>
                        <tr>
                            <th>Posição</th>
                            <th>Username</th>
                            <th>Nome</th>
                            <th>Dimensão</th>
                            <th>Modalidade</th>
                            <th>Pontuação</th>
                            <th>Nível</th>
                            <th>Data/Hora</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($ranking as $i => $linha): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= e($linha['username']) ?></td>
                            <td><?= e($linha['nome_completo']) ?></td>
                            <td><?= (int) $linha['linhas'] ?>x<?= (int) $linha['colunas'] ?></td>
                            <td><?= e($modalidades[$linha['modalidade']] ?? $linha['modalidade']) ?></td>
                            <td><?= number_format((int) $linha['pontuacao'], 0, ',', '.') ?></td>
                            <td><?= (int) $linha['nivel'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($linha['data_hora'])) ?></td>
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