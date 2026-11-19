document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("formularioEdicao");
  if (!form) return;

  const erroGeral = document.getElementById("erroGeral");
  const sucessoGeral = document.getElementById("sucessoGeral");
  const botao = form.querySelector("button[type=submit]");
  const textoBotao = botao.textContent;
  const DICA =
    "Corrija os campos em vermelho.";

  const regras = {
    nome: function (v) {
      v = v.trim();
      if (v === "") return "O campo nome é obrigatório.";
      if (v.length > 120) return "O nome deve ter no máximo 120 caracteres.";
      return "";
    },
    telefone: function (v) {
      v = v.trim();
      if (v === "") return "O campo telefone é obrigatório.";
      if (v.length > 15)
        return "O telefone deve ter no máximo 15 caracteres, exemplo: (11) 98765-4321.";
      return "";
    },
    email: function (v) {
      v = v.trim();
      if (v === "") return "O campo e-mail é obrigatório.";
      if (v.length > 120 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v))
        return "E-mail inválido.";
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
    botao.textContent = "Salvando...";

    try {
      const resposta = await fetch(form.action, {
        method: "POST",
        body: new FormData(form),
        headers: { "X-Requested-With": "XMLHttpRequest" },
        credentials: "same-origin",
      });
      const dados = await resposta.json();

      if (dados.ok) {
        mostrarSucesso(dados.mensagem || "Informações atualizadas com sucesso!");
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
      console.error("Erro ao atualizar dados:", erro);
      mostrarErroGeral("Não foi possível salvar as alterações. Tente novamente.");
    } finally {
      botao.disabled = false;
      botao.textContent = textoBotao;
    }
  });
});