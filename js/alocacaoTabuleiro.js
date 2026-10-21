// Tive que colocar essa função porque o grid não estava sendo gerado, mas resolveu.
document.addEventListener('DOMContentLoaded', function() {
    
    const tamanho = document.getElementById("selectTamanhoMenu");
    const gradeJogo = document.getElementById('gradeJogo');

    if (!tamanho || !gradeJogo) {
        console.error("Erro: Não encontrou o select de tamanho ou a gradeJogo no HTML.");
        return; 
    }

    function gerarTabuleiro(tamanhoSelecionado) {
        gradeJogo.innerHTML = ''; 

        const dimensao = parseInt(tamanhoSelecionado.split('x')[0]); 
        const totalBuracos = dimensao * dimensao;

        gradeJogo.style.display = 'grid';
        gradeJogo.style.gridTemplateColumns = `repeat(${dimensao}, 1fr)`;
        gradeJogo.style.gap = '10px';

        for (let i = 0; i < totalBuracos; i++) {
            const buracoDiv = document.createElement('div');
            buracoDiv.classList.add('buraco');

            const img = document.createElement('img');
            img.src = '../assets/imgs/hole.png';
            img.alt = 'Buraco Vazio';
            img.classList.add('imgElemento');
            
            img.style.width = '100%';
            img.style.height = 'auto';

            buracoDiv.appendChild(img);
            gradeJogo.appendChild(buracoDiv);
        }
    }

    gerarTabuleiro(tamanho.value);

    tamanho.addEventListener('change', function(evento) {
        gerarTabuleiro(evento.target.value);
    });

    // Adiciona o evento de clique no container principal do tabuleiro
document.getElementById("gradeJogo").addEventListener("click", function(evento) {
    // Verifica se o elemento que foi clicado contém a classe "imgElemento"
    if (evento.target.classList.contains("imgElemento")) {
        clicarElemento(evento); // Chama a função passando o evento
    }
});

    // Trouxe a função clicarElemento do modoExplosivo.js para cá, pq com ela no modoExplosivo.js não estava contabilizando os pontos de clique no javascritp do jogo.
    function clicarElemento(evento) {
        if (!jogoAtivo) {
            return;
        }

        const imagem = evento.target;

        if (imagem.dataset.ocupado !== "true") {
            return;
        }

        if (imagem.alt === "Toupeira") {
            pontuacao++;

            // Marca como não ocupado já aqui, para não poder clicar de novo nela
            imagem.dataset.ocupado = "false";

            // Mostra a imagem de acerto
            imagem.src = "../assets/imgs/steveAcertado.png";
            imagem.alt = "Toupeira Acertada";

            // Depois de um tempinho, volta para o buraco vazio
            setTimeout(function () {
                imagem.src = "../assets/imgs/hole.png";
                imagem.alt = "Buraco Vazio";
            }, 300); // tempo em milissegundos que o "acertou" fica na tela

        } else if (imagem.alt === "Bomba") {
            pontuacao--;
            imagem.dataset.ocupado = "false";
            imagem.src = "../assets/imgs/smoke.png";
            imagem.alt = "Bomba Explodida";
            setTimeout(function () {
                imagem.src = "../assets/imgs/hole.png";
                imagem.alt = "Buraco Vazio";
        }, 300);
        } else {
            return;
        }

        document.getElementById("pontuacao").textContent = pontuacao;
    }
});