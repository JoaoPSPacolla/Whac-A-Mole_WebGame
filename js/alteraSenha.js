document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("formularioSenha");
  if (!form) return;

  const erroGeral = document.getElementById("erroGeral");
  const sucessoGeral = document.getElementById("sucessoGeral");
  const botao = form.querySelector("button[type=submit]");
  const textoBotao = botao.textContent;
  const DICA =
    "Corrija os campos em vermelho.";

  const regras = {
    senha_atual: function (v) {
      if (v === "") return "Informe sua senha atual.";
      return "";
    },
    nova_senha: function (v) {
      if (v === "") return "Informe a nova senha.";
      if (v.length < 8) return "A nova senha deve ter no mínimo 8 caracteres.";
      if (v === campo("senha_atual").value)
        return "A nova senha não pode ser igual à senha atual.";
      return "";
    },
    confirmar_senha: function (v) {
      if (v === "") return "Confirme a nova senha.";
      if (v !== campo("nova_senha").value)
        return "A confirmação de senha não confere.";
      return "";
    },
  };

  function campo(nome) {
    return form.elements[nome];
  }

  function mostrarErroGeral(mensagem) {
    erroGeral.textContent = mensagem;
    erroGeral.hidden = mensagem === "";
  }

  function mostrarSucesso(mensagem) {
    sucessoGeral.textContent = mensagem;
    sucessoGeral.hidden = mensagem === "";
  }

  function atualizarDica() {
    if (erroGeral.textContent === DICA && !form.querySelector(".invalido")) {
      mostrarErroGeral("");
    }
  }

  function mostrarErro(nome, mensagem) {
    const input = campo(nome);
    const aviso = document.getElementById("erro-" + nome);
    aviso.textContent = mensagem;
    input.classList.toggle("invalido", mensagem !== "");
    input.setAttribute("aria-invalid", mensagem !== "" ? "true" : "false");
    if (mensagem !== "") {
      input.setAttribute("aria-describedby", aviso.id);
    } else {
      input.removeAttribute("aria-describedby");
    }
    atualizarDica();
  }

  function validarCampo(nome) {
    const mensagem = regras[nome](campo(nome).value);
    mostrarErro(nome, mensagem);
    return mensagem === "";
  }

  Object.keys(regras).forEach(function (nome) {
    const input = campo(nome);
    input.addEventListener("blur", function () {
      validarCampo(nome);
    });
    input.addEventListener("input", function () {
      mostrarSucesso("");
      if (input.classList.contains("invalido")) {
        validarCampo(nome);
      }
      if (nome === "senha_atual" && campo("nova_senha").value !== "") {
        validarCampo("nova_senha");
      }
      if (nome === "nova_senha" && campo("confirmar_senha").value !== "") {
        validarCampo("confirmar_senha");
      }
    });
  });

  form.addEventListener("submit", async function (evento) {
    evento.preventDefault();
    mostrarErroGeral("");
    mostrarSucesso("");

    let primeiroInvalido = null;
    Object.keys(regras).forEach(function (nome) {
      if (!validarCampo(nome) && !primeiroInvalido) {
        primeiroInvalido = campo(nome);
      }
    });

    if (primeiroInvalido) {
      mostrarErroGeral(DICA);
      primeiroInvalido.focus();
      return;
    }

    botao.disabled = true;
    botao.textContent = "Alterando...";

    try {
      const resposta = await fetch(form.action, {
        method: "POST",
        body: new FormData(form),
        headers: { "X-Requested-With": "XMLHttpRequest" },
        credentials: "same-origin",
      });
      const dados = await resposta.json();

      if (dados.ok) {
        form.reset();
        mostrarSucesso(dados.mensagem || "Senha alterada com sucesso!");
        return;
      }

      const erros = dados.erros || {};
      let primeiro = null;
      Object.keys(erros).forEach(function (nome) {
        if (nome === "geral") {
          mostrarErroGeral(erros[nome]);
        } else if (campo(nome)) {
          mostrarErro(nome, erros[nome]);
          if (!primeiro) primeiro = campo(nome);
        }
      });
      if (primeiro) {
        if (erroGeral.hidden) mostrarErroGeral(DICA);
        primeiro.focus();
      }
    } catch (erro) {
      console.error("Erro ao alterar senha:", erro);
      mostrarErroGeral("Não foi possível alterar a senha. Tente novamente.");
    } finally {
      botao.disabled = false;
      botao.textContent = textoBotao;
    }
  });
});