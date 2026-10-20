// Tive que colocar essa função porque o grid não estava sendo gerado, mas resolveu.
document.addEventListener('DOMContentLoaded', function() {
    
    const selectTamanho = document.getElementById('tamanho');
    const gradeJogo = document.getElementById('gradeJogo');

    if (!selectTamanho || !gradeJogo) {
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

    gerarTabuleiro(selectTamanho.value);

    selectTamanho.addEventListener('change', function(evento) {
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

        // Mudamos de currentTarget para target, pois o target é exatamente o elemento clicado
        const imagem = evento.target;

        if (imagem.dataset.ocupado !== "true") {
            return;
        }

        if (imagem.alt === "Toupeira") {
            pontuacao++;
        } else if (imagem.alt === "Bomba") {
            pontuacao--;
        } else {
            return;
        }

        document.getElementById("pontuacao").textContent = pontuacao;

        // Faz o personagem desaparecer imediatamente.
        imagem.src = "../assets/imgs/hole.png";
        imagem.alt = "Buraco Vazio";
        imagem.dataset.ocupado = "false";
    }
});