
var temporizadorVisual;
var intervalo;
var temporizadorFim;
var jogoAtivo = false;

var pontuacao = 0;
var toupeirasAparecidas = 0;
var identificadorAparicao = 0;
var faseAtual = 0;

// Todos os tempos estão em milissegundos.
const FASES = [
    {
        duracao: 30000,
        tempoVisivel: 1500,
        tempoEntreAparicoes: 1000,
        percentualMinimo: 70
    },
    {
        duracao: 30000,
        tempoVisivel: 1100,
        tempoEntreAparicoes: 800,
        percentualMinimo: 75
    },
    {
        duracao: 30000,
        tempoVisivel: 500,
        tempoEntreAparicoes: 400,
        percentualMinimo: 80
    }
];

// Inicia uma nova partida.
function iniciar() {
    if (jogoAtivo) {
        return;
    }

    faseAtual = 0;
    iniciarFase();
}

// Prepara e inicia a fase atual.
function iniciarFase() {
    if (faseAtual >= FASES.length) {
        jogoAtivo = false;
        alert("Parabéns! Você venceu todas as fases!");
        return;
    }

    const fase = FASES[faseAtual];

    pontuacao = 0;
    toupeirasAparecidas = 0;

    document.getElementById("nivel").textContent = faseAtual + 1;
    document.getElementById("pontuacao").textContent = 0;
    document.getElementById("quantidadeToupeiras").textContent = 0;

    document.getElementById("proximo").textContent =
        fase.percentualMinimo + "%";

    // Limpa personagens que possam ter restado da fase anterior.
    document.querySelectorAll(".imgElemento").forEach(function (imagem) {
        imagem.src = "../assets/imgs/hole.png";
        imagem.alt = "Buraco Vazio";
        imagem.dataset.ocupado = "false";

        // Invalida temporizadores de aparições anteriores.
        identificadorAparicao++;
        imagem.dataset.aparicao = String(identificadorAparicao);
    });

    jogoAtivo = true;

    // Inicia o temporizador visual com a duração da fase.
    const inicio = Date.now();

    function atualizarTemporizador() {
        const tempoDecorrido = Date.now() - inicio;

        const tempoRestante = Math.max(
            0,
            Math.ceil((fase.duracao - tempoDecorrido) / 1000)
        );

        const minutos = Math.floor(tempoRestante / 60);
        const segundos = tempoRestante % 60;

        document.getElementById("timer").textContent =
            String(minutos).padStart(2, "0") + ":" +
            String(segundos).padStart(2, "0");
    }

    atualizarTemporizador();

    temporizadorVisual = setInterval(
        atualizarTemporizador,
        200
    );

    // Inicia as aparições conforme a configuração da fase.
    trocarFoto();

    intervalo = setInterval(
        trocarFoto,
        fase.tempoEntreAparicoes
    );

    // Encerra a fase ao terminar sua duração.
    temporizadorFim = setTimeout(function () {
        clearInterval(intervalo);
        clearInterval(temporizadorVisual);

        jogoAtivo = false;

        checarProgresso();
    }, fase.duracao);
}

// Avalia o desempenho e decide se o jogador avança.
function checarProgresso() {
    const fase = FASES[faseAtual];

    const aproveitamento = toupeirasAparecidas > 0
        ? (pontuacao / toupeirasAparecidas) * 100
        : 0;

    const aproveitamentoFormatado = aproveitamento.toFixed(2);

    if (aproveitamento >= fase.percentualMinimo) {
        if (typeof window.exibirTelaVitoria === "function") {
            window.exibirTelaVitoria(
                {
                    fase: faseAtual + 1,
                    pontuacao: pontuacao,
                    toupeiras: toupeirasAparecidas,
                    aproveitamento: aproveitamentoFormatado,
                    meta: fase.percentualMinimo
                },
                function () {
                    faseAtual++;
                    iniciarFase();
                }
            );
        } else {
            faseAtual++;
            iniciarFase();
        }
    } else {
        // Substituído o alert tradicional pela Tela de Morte do Minecraft
        if (typeof window.exibirTelaDerrota === "function") {
            window.exibirTelaDerrota({
                fase: faseAtual + 1,
                pontuacao: pontuacao,
                toupeiras: toupeirasAparecidas,
                aproveitamento: aproveitamentoFormatado,
                meta: fase.percentualMinimo
            });
        } else {
            alert("Fim de jogo! Você não atingiu a meta.");
        }
    }
}

// Sorteia um personagem para aparecer em um buraco vazio.
function trocarFoto() {
    if (!jogoAtivo) {
        return;
    }

    const fase = FASES[faseAtual];
    const buracos = document.querySelectorAll(".buraco");
    const buracosVazios = [];

    buracos.forEach(function (buraco) {
        const imagem = buraco.querySelector("img");

        if (imagem.dataset.ocupado !== "true") {
            buracosVazios.push(buraco);
        }
    });

    if (buracosVazios.length === 0) {
        return;
    }

    const sorteio = Math.floor(Math.random() * buracosVazios.length);
    const buracoEscolhido = buracosVazios[sorteio];
    const escolha = buracoEscolhido.querySelector("img");

    // 0 = nenhuma aparição; 1 = bomba; 2 = toupeira.
    const sorteioImagem = Math.floor(Math.random() * 3);

    if (sorteioImagem === 0) {
        return;
    }

    if (sorteioImagem === 1) {
        escolha.src = "../assets/imgs/creeper.png";
        escolha.alt = "Bomba";
    } else {
        escolha.src = "../assets/imgs/steve.png";
        escolha.alt = "Toupeira";

        toupeirasAparecidas++;

        document.getElementById("quantidadeToupeiras").textContent =
            toupeirasAparecidas;
    }

    escolha.dataset.ocupado = "true";

    // Cada aparição recebe um identificador próprio.
    identificadorAparicao++;
    const aparicaoAtual = String(identificadorAparicao);

    escolha.dataset.aparicao = aparicaoAtual;

    // Remove somente esta aparição, sem interferir nas seguintes.
    setTimeout(function () {
        if (
            escolha.dataset.aparicao === aparicaoAtual &&
            escolha.dataset.ocupado === "true"
        ) {
            escolha.src = "../assets/imgs/hole.png";
            escolha.alt = "Buraco Vazio";
            escolha.dataset.ocupado = "false";
        }
    }, fase.tempoVisivel);
}