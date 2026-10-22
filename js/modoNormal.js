// Inicia uma nova partida no modo normal.
function iniciarNormal() {
    resetarPartida();
    iniciarFaseNormal();
}

function iniciarFaseNormal() {

    const fase = obterFase(faseAtual);

    pontuacao = 0;
    toupeirasAparecidas = 0;

    document.getElementById("nivel").textContent = faseAtual + 1;
    document.getElementById("pontuacao").textContent = 0;
    document.getElementById("quantidadeToupeiras").textContent = 0;
    document.getElementById("proximo").textContent = fase.percentualMinimo + "%";

    document.querySelectorAll(".imgElemento").forEach(function (imagem) {
        imagem.src = "../assets/imgs/hole.png";
        imagem.alt = "Buraco Vazio";
        imagem.dataset.ocupado = "false";

        identificadorAparicao++;
        imagem.dataset.aparicao = String(identificadorAparicao);
    });

    jogoAtivo = true;

    const inicio = Date.now();

    function atualizarTemporizador() {
        const tempoDecorrido = Date.now() - inicio;
        const tempoRestante = Math.max(0, Math.ceil((fase.duracao - tempoDecorrido) / 1000));
        const minutos = Math.floor(tempoRestante / 60);
        const segundos = tempoRestante % 60;

        document.getElementById("timer").textContent =
            String(minutos).padStart(2, "0") + ":" + String(segundos).padStart(2, "0");
    }

    atualizarTemporizador();
    temporizadorVisual = setInterval(atualizarTemporizador, 200);

    trocarFotoNormal();
    intervalo = setInterval(trocarFotoNormal, fase.tempoEntreAparicoes);

    temporizadorFim = setTimeout(function () {
        clearInterval(intervalo);
        clearInterval(temporizadorVisual);
        jogoAtivo = false;
        checarProgressoNormal();
    }, fase.duracao);
}

function checarProgressoNormal() {
    const fase = obterFase(faseAtual);

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
                    iniciarFaseNormal();
                }
            );
        } else {
            faseAtual++;
            iniciarFaseNormal();
        }
    } else {
        if (typeof window.exibirTelaDerrota === "function") {
            window.exibirTelaDerrota({
                fase: faseAtual + 1,
                pontuacao: pontuacao,
                toupeiras: toupeirasAparecidas,
                aproveitamento: aproveitamentoFormatado,
                meta: fase.percentualMinimo
            });
        }
    }
}

// Sorteia um personagem para aparecer em um buraco vazio (sem bomba).
function trocarFotoNormal() {
    if (!jogoAtivo) {
        return;
    }

    const fase = obterFase(faseAtual);
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

    // 0 = nenhuma aparição; 1 = toupeira. Sem bomba no modo normal.
    const sorteioImagem = Math.floor(Math.random() * 2);

    if (sorteioImagem === 0) {
        return;
    }

    escolha.src = "../assets/imgs/steve.png";
    escolha.alt = "Toupeira";

    toupeirasAparecidas++;
    document.getElementById("quantidadeToupeiras").textContent = toupeirasAparecidas;

    escolha.dataset.ocupado = "true";

    identificadorAparicao++;
    const aparicaoAtual = String(identificadorAparicao);
    escolha.dataset.aparicao = aparicaoAtual;

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