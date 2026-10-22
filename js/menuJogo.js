document.addEventListener("DOMContentLoaded", () => {

    // Configurações do jogo
    const TAMANHOS = [2, 3, 4, 5, 6, 7, 8];
    const TAMANHO_PADRAO = "3x3";
    const MODOS = [
        { valor: "normal", rotulo: "Normal" },
        { valor: "explosivo", rotulo: "Explosivo" },
    ];

    const containerJogo = document.querySelector(".containerJogo");
    if (!containerJogo) {
        console.error("Erro: Container do jogo (.containerJogo) não encontrado.");
        return;
    }

    const gradeJogo = document.getElementById("gradeJogo");
    gradeJogo?.addEventListener("dragstart", (e) => e.preventDefault());

    // Criação dos componentes reutilizáveis para seleção de opções e botões
    function criarSelect(id, opcoes, valorSelecionado) {
        const select = document.createElement("select");
        select.id = id;
        select.className = "select-mc";

        opcoes.forEach(({ valor, rotulo }) => {
            const option = document.createElement("option");
            option.value = valor;
            option.textContent = rotulo;
            select.append(option);
        });

        if (valorSelecionado) select.value = valorSelecionado;
        return select;
    }

    function criarGrupo(idSelect, textoRotulo, select) {
        const grupo = document.createElement("div");
        grupo.className = "grupo-opcao-menu";

        const rotulo = document.createElement("label");
        rotulo.className = "rotulo-opcao-menu";
        rotulo.htmlFor = idSelect;
        rotulo.textContent = textoRotulo;

        grupo.append(rotulo, select);
        return grupo;
    }

    function criarBotao(texto, onClick) {
        const botao = document.createElement("button");
        botao.type = "button";
        botao.className = "btn-mc";
        botao.textContent = texto;
        botao.addEventListener("click", onClick);
        return botao;
    }

    function criarTela({ id, classe, titulo, destaque, linhas, botoes }) {
        document.getElementById(id)?.remove();

        const overlay = document.createElement("div");
        overlay.id = id;
        overlay.className = `overlay-tabuleiro-minecraft ${classe}`;
        overlay.setAttribute("role", "dialog");

        const caixa = document.createElement("div");
        caixa.className = "caixa-menu-minecraft caixa-resultado";

        const h2 = document.createElement("h2");
        h2.id = `${id}-titulo`;
        h2.className = "titulo-menu-minecraft";
        h2.textContent = titulo;
        caixa.append(h2);

        if (destaque) {
            const p = document.createElement("p");
            p.className = "destaque-resultado";
            p.append(`${destaque.rotulo}: `);

            const valor = document.createElement("span");
            valor.textContent = destaque.valor;
            p.append(valor);
            caixa.append(p);
        }

        const stats = document.createElement("div");
        stats.className = "stats-resultado";
        linhas.forEach(({ rotulo, valor, classe }) => {
            const p = document.createElement("p");
            if (classe) p.className = classe;
            p.textContent = `${rotulo}: ${valor}`;
            stats.append(p);
        });
        caixa.append(stats);

        botoes.forEach(({ texto, onClick }) => {
            caixa.append(
                criarBotao(texto, () => {
                    overlay.remove();
                    onClick?.();
                })
            );
        });

        overlay.append(caixa);
        containerJogo.append(overlay);
        caixa.querySelector("button")?.focus();
        return overlay;
    }


    //Menu inicial
    const selectModo = criarSelect(
        "selectModoJogo",
        MODOS
    );

    const selectTamanhoMenu = criarSelect(
        "selectTamanhoMenu",
        TAMANHOS.map((n) => ({
            valor: `${n}x${n}`,
            rotulo: `${n} x ${n} (${n * n} buracos)`,
        }))
    );

    const btnJogar = criarBotao("Jogar", iniciarPeloMenu);
    btnJogar.id = "btnConfirmarEIniciar";
    btnJogar.classList.add("btn-jogar");

    const overlayMenu = document.createElement("div");
    overlayMenu.id = "overlayMenuJogo";
    overlayMenu.className = "overlay-tabuleiro-minecraft";
    overlayMenu.setAttribute("role", "dialog");

    const caixaMenu = document.createElement("div");
    caixaMenu.className = "caixa-menu-minecraft";

    const tituloMenu = document.createElement("h2");
    tituloMenu.id = "tituloMenuJogo";
    tituloMenu.className = "titulo-menu-minecraft";
    tituloMenu.textContent = "Menu do Jogo";

    caixaMenu.append(
        tituloMenu,
        criarGrupo("selectModoJogo", "Modo de Jogo:", selectModo),
        criarGrupo("selectTamanhoMenu", "Tamanho do Tabuleiro:", selectTamanhoMenu),
        btnJogar
    );
    overlayMenu.append(caixaMenu);
    containerJogo.append(overlayMenu);

    // Ações do menu e início do jogo
    function abrirMenu() {
        overlayMenu.classList.remove("menu-oculto");
    }

    function ocultarMenu() {
        overlayMenu.classList.add("menu-oculto");
    }

    function iniciarJogo() {
    const modoEscolhido = selectModo.value;

    if (modoEscolhido === "normal") {
        if (typeof iniciarNormal !== "function") {
            console.error("A função iniciarNormal() não foi encontrada.");
            return false;
        }
        iniciarNormal();
    } else {
        if (typeof iniciar !== "function") {
            console.error("A função iniciar() não foi encontrada.");
            return false;
        }
        iniciar();
    }

    return true;
    }

    function iniciarPeloMenu() {
        const modoEscolhido = selectModo.value;
        const funcaoExiste = modoEscolhido === "normal"
            ? typeof iniciarNormal === "function"
            : typeof iniciar === "function";

        if (!funcaoExiste) {
            console.error("A função do modo selecionado não foi encontrada.");
            return;
        }

        ocultarMenu();
        iniciarJogo();
    }

    // Aplicação do tamanho selecionado ao tabuleiro
    function aplicarTamanho(valor) {
        const dimensao = Number.parseInt(valor, 10);
        if (!TAMANHOS.includes(dimensao) || !gradeJogo) return;

        gradeJogo.style.setProperty("--n", dimensao);
        gradeJogo.dataset.tamanho = dimensao;
    }

    selectTamanhoMenu.value = TAMANHO_PADRAO;
    aplicarTamanho(TAMANHO_PADRAO);

    selectTamanhoMenu.addEventListener("change", () => {
        aplicarTamanho(selectTamanhoMenu.value);
    });

    //Janelas de vitoria e derroa
    window.exibirTelaVitoria = function (dados, callbackContinuar) {
        criarTela({
            id: "overlayTelaVitoria",
            classe: "overlay-vitoria",
            titulo: dados.eUltimaFase ? "Você Venceu!" : "Fase Concluída!",
            destaque: { rotulo: "Fase Completa", valor: dados.fase },
            linhas: [
                { rotulo: "Pontuação", valor: dados.pontuacao },
                { rotulo: "Toupeiras que apareceram", valor: dados.toupeiras },
                { rotulo: "Aproveitamento", valor: `${dados.aproveitamento}%` },
                { rotulo: "Meta atingida", valor: `${dados.meta}%`, classe: "stat-sucesso" },
            ],
            botoes: [
                {
                    texto: dados.eUltimaFase ? "Menu Principal" : "Continuar",
                    onClick: () => {
                        if (typeof callbackContinuar === "function") callbackContinuar();
                    },
                },
            ],
        });
    };

    window.exibirTelaDerrota = function (dados) {
        criarTela({
            id: "overlayTelaDerrota",
            classe: "overlay-derrota",
            titulo: "Fim de jogo!",
            destaque: { rotulo: "Pontuação", valor: dados.pontuacao },
            linhas: [
                { rotulo: "Fase alcançada", valor: dados.fase },
                { rotulo: "Toupeiras que apareceram", valor: dados.toupeiras },
                { rotulo: "Aproveitamento", valor: `${dados.aproveitamento}%` },
                { rotulo: "Meta necessária", valor: `${dados.meta}%`, classe: "stat-falha" },
            ],
            botoes: [
                { texto: "Jogar Novamente", onClick: iniciarJogo },
                { texto: "Menu Principal", onClick: abrirMenu },
            ],
        });
    };

    window.abrirMenuJogo = abrirMenu;

});