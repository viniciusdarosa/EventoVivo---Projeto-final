/* ============================================================
   EventoVivo — menu.js
   Menu móvel acessível do cabeçalho + estados de carregamento
   dos formulários principais.

   Progressive enhancement: sem JS, o botão hambúrguer não
   aparece e o menu continua visível (regras do site.css).
   ============================================================ */
(function () {
  'use strict';

  var botao = document.querySelector('.menu-toggle');
  var nav = document.getElementById('nav-principal');

  /* ----------------------------------------------------------
     1. MENU MÓVEL
     ---------------------------------------------------------- */
  if (botao && nav) {
    var AbrirMenu = function () {
      nav.classList.add('is-open');
      botao.setAttribute('aria-expanded', 'true');
      botao.setAttribute('aria-label', 'Fechar menu de navegação');
      document.body.classList.add('menu-aberto');
    };

    var FecharMenu = function (devolverFoco) {
      nav.classList.remove('is-open');
      botao.setAttribute('aria-expanded', 'false');
      botao.setAttribute('aria-label', 'Abrir menu de navegação');
      document.body.classList.remove('menu-aberto');
      if (devolverFoco) {
        botao.focus();
      }
    };

    var EstaAberto = function () {
      return botao.getAttribute('aria-expanded') === 'true';
    };

    botao.addEventListener('click', function () {
      if (EstaAberto()) {
        FecharMenu(false);
      } else {
        AbrirMenu();
      }
    });

    // Escape fecha o menu e devolve o foco ao botão.
    document.addEventListener('keydown', function (evento) {
      if (evento.key === 'Escape' && EstaAberto()) {
        FecharMenu(true);
      }
    });

    // Clique fora do menu (no cabeçalho ou no conteúdo) fecha.
    document.addEventListener('click', function (evento) {
      if (!EstaAberto()) {
        return;
      }
      if (!nav.contains(evento.target) && !botao.contains(evento.target)) {
        FecharMenu(false);
      }
    });

    // Ao escolher uma opção, o menu fecha antes da navegação.
    nav.addEventListener('click', function (evento) {
      var alvo = evento.target;
      while (alvo && alvo !== nav) {
        if (alvo.tagName === 'A') {
          FecharMenu(false);
          return;
        }
        alvo = alvo.parentNode;
      }
    });

    // Ao voltar para desktop, o menu volta ao estado padrão.
    var mediaDesktop = window.matchMedia('(min-width: 1100px)');
    var AoMudarLargura = function (evento) {
      if (evento.matches && EstaAberto()) {
        FecharMenu(false);
      }
    };
    if (mediaDesktop.addEventListener) {
      mediaDesktop.addEventListener('change', AoMudarLargura);
    } else if (mediaDesktop.addListener) {
      mediaDesktop.addListener(AoMudarLargura); // navegadores antigos
    }
  }

  /* ----------------------------------------------------------
     2. ESTADO DE CARREGAMENTO DOS FORMULÁRIOS PRINCIPAIS
     Evita duplo envio. Só nos formulários marcados com
     data-loading, para não interferir em forms com confirm().
     ---------------------------------------------------------- */
  var formuladores = document.querySelectorAll('form[data-loading="true"]');
  Array.prototype.forEach.call(formuladores, function (form) {
    form.addEventListener('submit', function (evento) {
      if (form.dataset.enviando === '1') {
        evento.preventDefault();
        return;
      }
      form.dataset.enviando = '1';

      var botaoEnviar = form.querySelector('[type="submit"]');
      if (botaoEnviar) {
        if (!botaoEnviar.dataset.rotuloOriginal) {
          botaoEnviar.dataset.rotuloOriginal = botaoEnviar.textContent;
        }
        botaoEnviar.setAttribute('aria-disabled', 'true');
        botaoEnviar.disabled = true;
        botaoEnviar.innerHTML = '<span class="spinner" aria-hidden="true"></span> Enviando...';
      }
    });
  });
})();
