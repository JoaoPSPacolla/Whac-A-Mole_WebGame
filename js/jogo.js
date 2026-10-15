function iniciar(){
    trocarFoto();
    setInterval(trocarFoto, 1000);
    setTimeout(trocarFoto, 60000); //60 segundos
}

function trocarFoto(){
    var buracos = document.querySelectorAll(".buraco");
    var sorteio_buraco = Math.floor(Math.random()*9);
    var sorteio_imagem = Math.floor(Math.random()*3)

    console.log(sorteio_buraco)
    console.log(sorteio_imagem)

    var escolha = buracos[sorteio_buraco].querySelector("img")

    if (sorteio_imagem == 0){
        escolha.src = "../assets/imgs/hole.png"
    }
    else if (sorteio_imagem == 1){
        escolha.src = "../assets/imgs/creeper.png"
    }else{
        escolha.src = "../assets/imgs/steve.png"
    }
}

