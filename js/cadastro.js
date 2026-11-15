// Validação do formulário de cadastro na própria página.
// As regras espelham as de php/cadastro.php (o servidor continua validando tudo).
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("formularioCadastro");
  if (!form) {
    return;
  }

  const erroGeral = document.getElementById("erroGeral");
  const botao = form.querySelector("button[type=submit]");
  const textoBotao = botao.textContent;
  const DICA =
    "Corrija os campos em vermelho. Passe o mouse ou clique neles para ver o que está errado.";

  // Cada regra recebe o valor do campo e devolve a mensagem de erro ("" = válido).
  const regras = {
    nome: function (v) {
      v = v.trim();
      if (v === "") return "O campo nome é obrigatório.";
      if (v.length > 120) return "O nome deve ter no máximo 120 caracteres.";
      return "";
    },
    data: function (v) {
      if (v === "") return "O campo data de nascimento é obrigatório.";
      if (v > dataDeHoje())
        return "A data de nascimento não pode ser maior que a data atual.";
      return "";
    },
    cpf: function (v) {
      v = v.trim();
      if (v === "") return "O campo CPF é obrigatório.";
      if (v.replace(/\D/g, "").length !== 11)
        return "O CPF deve conter 11 dígitos.";
      return "";
    },
    telefone: function (v) {
      v = v.trim();
      if (v === "") return "O campo telefone é obrigatório.";
      if (v.length > 20) return "O telefone deve ter no máximo 20 caracteres.";
      return "";
    },
    email: function (v) {
      v = v.trim();
      if (v === "") return "O campo e-mail é obrigatório.";
      if (v.length > 120 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v))
        return "E-mail inválido.";
      return "";
    },
    username: function (v) {
      v = v.trim();
      if (v === "") return "O campo username é obrigatório.";
      if (!/^\w{3,30}$/.test(v))
        return "O username deve ter de 3 a 30 caracteres, usando apenas letras, números e _.";
      return "";
    },
    senha: function (v) {
      if (v === "") return "O campo senha é obrigatório.";
      if (v.length < 8) return "A senha deve ter no mínimo 8 caracteres.";
      return "";
    },
    confirmar_senha: function (v) {
      if (v === "") return "Confirme a senha.";
      if (v !== campo("senha").value)
        return "A confirmação de senha não confere.";
      return "";
    },
  };

  function campo(nome) {
    return form.elements[nome];
  }

  function dataDeHoje() {
    const hoje = new Date();
    const mes = String(hoje.getMonth() + 1).padStart(2, "0");
    const dia = String(hoje.getDate()).padStart(2, "0");
    return hoje.getFullYear() + "-" + mes + "-" + dia;
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

  // Some com o aviso de orientação quando não resta nenhum campo com erro.
  function atualizarDica() {
    if (erroGeral.textContent === DICA && !form.querySelector(".invalido")) {
      mostrarErroGeral("");
    }
  }

  // Valida um campo e mostra/limpa a mensagem. Devolve true se estiver válido.
  function validarCampo(nome) {
    const mensagem = regras[nome](campo(nome).value);
    mostrarErro(nome, mensagem);
    return mensagem === "";
  }

  function mostrarErroGeral(mensagem) {
    erroGeral.textContent = mensagem;
    erroGeral.hidden = mensagem === "";
  }

  Object.keys(regras).forEach(function (nome) {
    const input = campo(nome);

    // Ao sair do campo, valida.
    input.addEventListener("blur", function () {
      validarCampo(nome);
    });

    // Enquanto digita, só revalida se o campo já estava com erro (some assim que corrigir).
    input.addEventListener("input", function () {
      if (input.classList.contains("invalido")) {
        validarCampo(nome);
      }
      // Mudar a senha pode tornar a confirmação válida/inválida.
      if (nome === "senha" && campo("confirmar_senha").value !== "") {
        validarCampo("confirmar_senha");
      }
    });
  });

  form.addEventListener("submit", async function (evento) {
    evento.preventDefault();
    mostrarErroGeral("");

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
    botao.textContent = "Cadastrando...";

    try {
      const resposta = await fetch(form.action, {
        method: "POST",
        body: new FormData(form),
        headers: { "X-Requested-With": "XMLHttpRequest" },
        credentials: "same-origin",
      });
      const dados = await resposta.json();

      if (dados.ok) {
        window.location.href = dados.redirect || "../index.php";
        return;
      }

      // Erros vindos do servidor (por campo; "geral" é para erros sem campo).
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
      console.error("Erro ao enviar o cadastro:", erro);
      mostrarErroGeral(
        "Não foi possível concluir o cadastro. Tente novamente.",
      );
    } finally {
      botao.disabled = false;
      botao.textContent = textoBotao;
    }
  });
});
