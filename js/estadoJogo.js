var temporizadorVisual;
var intervalo;
var temporizadorFim;
var jogoAtivo = false;

var pontuacao = 0;
var toupeirasAparecidas = 0;
var identificadorAparicao = 0;
var faseAtual = 0;

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
            Math.round(TEMPO_VISIVEL_INICIAL * Math.pow(0.8, indice))
        ),
        tempoEntreAparicoes: Math.max(
            TEMPO_ENTRE_APARICOES_MINIMO,
            Math.round(TEMPO_ENTRE_APARICOES_INICIAL / Math.pow(1.1, indice))
        ),
        // Sobe 5 por fase e para em 90
        percentualMinimo: Math.min(
            PERCENTUAL_FINAL,
            PERCENTUAL_INICIAL + indice * PASSO_PERCENTUAL
        )
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

    document.querySelectorAll(".imgElemento").forEach(function (imagem) {
        imagem.src = "../assets/imgs/hole.png";
        imagem.alt = "Buraco Vazio";
        imagem.dataset.ocupado = "false";
        identificadorAparicao++;
        imagem.dataset.aparicao = String(identificadorAparicao);
    });
}
