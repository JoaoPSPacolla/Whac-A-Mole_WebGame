var temporizadorVisual;
var intervalo;
var temporizadorFim;
var jogoAtivo = false;

var pontuacao = 0;
var toupeirasAparecidas = 0;
var identificadorAparicao = 0;
var faseAtual = 0;

// Dados da partida inteira (pontuacao e toupeirasAparecidas são zeradas a cada fase)
var pontuacaoTotal = 0;
var toupeirasTotal = 0;
var modoAtual = "classica"; // "classica" | "explosiva" (valores do ENUM da tabela partidas)
var linhasAtuais = 3;
var colunasAtuais = 3;
var partidaEmAndamento = false;

// Todos os tempos estão em milissegundos.
const DURACAO_FASE = 30000;
const TEMPO_VISIVEL_INICIAL = 1500;
const TEMPO_ENTRE_APARICOES_INICIAL = 1000;

const PERCENTUAL_INICIAL = 60;
const PERCENTUAL_FINAL = 80;
const PASSO_PERCENTUAL = 5;

// Gera as fases: a cada fase o tempo visível cai 20%, a frequência de
// aparições sobe 10% e a meta de acerto sobe 5 pontos até noventa.
const TEMPO_VISIVEL_MINIMO = 150;
const TEMPO_ENTRE_APARICOES_MINIMO = 150;

function obterFase(indice) {
  return {
    duracao: DURACAO_FASE,
    tempoVisivel: Math.max(
      TEMPO_VISIVEL_MINIMO,
      Math.round(TEMPO_VISIVEL_INICIAL * Math.pow(0.8, indice)),
    ),
    tempoEntreAparicoes: Math.max(
      TEMPO_ENTRE_APARICOES_MINIMO,
      Math.round(TEMPO_ENTRE_APARICOES_INICIAL / Math.pow(1.1, indice)),
    ),
    // Sobe 5 por fase e para em 90
    percentualMinimo: Math.min(
      PERCENTUAL_FINAL,
      PERCENTUAL_INICIAL + indice * PASSO_PERCENTUAL,
    ),
  };
}

function resetarPartida() {
  clearInterval(intervalo);
  clearInterval(temporizadorVisual);
  clearTimeout(temporizadorFim);

  jogoAtivo = false;
  pontuacao = 0;
  toupeirasAparecidas = 0;
  faseAtual = 0;
  pontuacaoTotal = 0;
  toupeirasTotal = 0;
  partidaEmAndamento = false;

  document.querySelectorAll(".imgElemento").forEach(function (imagem) {
    imagem.src = "../assets/imgs/hole.png";
    imagem.alt = "Buraco Vazio";
    imagem.dataset.ocupado = "false";
    identificadorAparicao++;
    imagem.dataset.aparicao = String(identificadorAparicao);
  });
}

// Chamada quando uma partida começa: guarda modo e tamanho do tabuleiro.
function registrarInicioPartida(modo) {
  const grade = document.getElementById("gradeJogo");
  const dimensao = parseInt(grade.dataset.tamanho, 10) || 3;

  modoAtual = modo;
  linhasAtuais = dimensao;
  colunasAtuais = dimensao;
  pontuacaoTotal = 0;
  toupeirasTotal = 0;
  partidaEmAndamento = true;
}

// Envia a partida para o banco. Só envia uma vez por partida.
// status: "finalizada" (perdeu) ou "desistencia" (saiu da página no meio do jogo).
function salvarPartida(status) {
  if (!partidaEmAndamento) {
    return;
  }
  partidaEmAndamento = false;

  const dados = {
    linhas: linhasAtuais,
    colunas: colunasAtuais,
    modalidade: modoAtual,
    // Se a fase ainda está rodando (jogoAtivo), os pontos dela ainda não entraram no total.
    pontuacao: pontuacaoTotal + (jogoAtivo ? pontuacao : 0),
    toupeiras: toupeirasTotal + (jogoAtivo ? toupeirasAparecidas : 0),
    nivel: faseAtual + 1,
    status: status,
  };

  fetch("../php/salvarPartida.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "same-origin",
    keepalive: true, // permite terminar o envio mesmo se a página estiver fechando
    body: JSON.stringify(dados),
  })
    .then(function (resposta) {
      return resposta
        .json()
        .catch(function () {
          return {};
        })
        .then(function (corpo) {
          if (!resposta.ok || !corpo.ok) {
            console.error(
              "Não foi possível salvar a partida:",
              corpo.erro || resposta.status,
            );
          }
        });
    })
    .catch(function (erro) {
      console.error("Erro de rede ao salvar a partida:", erro);
    });
}

// Fechar/recarregar/sair da página no meio de uma partida conta como desistência.
window.addEventListener("pagehide", function () {
  salvarPartida("desistencia");
});
