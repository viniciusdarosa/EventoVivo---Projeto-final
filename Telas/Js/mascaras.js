/* ==========================================================
 * Js/mascaras.js — Máscaras de entrada do EventoVivo.
 *
 * Telefone BR (fixo/celular com DDD), CPF e CEP.
 *
 * A formatação é sempre REFEITA a partir dos dígitos digitados
 * (nunca concatena máscara sobre máscara), portanto:
 *   - não duplica caracteres ao digitar/colar;
 *   - Backspace/Delete apagam dígitos mesmo com o cursor ao lado
 *     da pontuação (a máscara é refeita em seguida);
 *   - o cursor é reposicionado logo após a MESMA quantidade de
 *     dígitos de antes da edição — edição no meio funciona;
 *   - colagem com DDI 55, pontuação ou formatação é normalizada.
 *
 * O servidor (funcoes_contato.php) valida e normaliza de novo:
 * este arquivo é apenas conforto — nunca a única barreira.
 * ========================================================== */
(function (global) {
  'use strict';

  function digitos(texto) {
    if (texto === null || texto === undefined) {
      texto = '';
    }
    return (String(texto).match(/\d/g) || []).join('');
  }

  /* ---------- Telefone ---------- */

  function normalizarTelefone(d) {
    // Colado com DDI 55: 12 ou 13 dígitos no total.
    if ((d.length === 12 || d.length === 13) && d.substring(0, 2) === '55') {
      d = d.substring(2);
    }
    return d.length > 11 ? d.substring(0, 11) : d;
  }

  function formatarTelefone(d) {
    if (!d) {
      return '';
    }
    if (d.length <= 2) {
      return '(' + d + (d.length === 2 ? ')' : '');
    }
    var ddd = d.substring(0, 2);
    var resto = d.substring(2);
    // 10 digitos = fixo (4+4); 11 = celular (5+4). Mesmo critério do
    // servidor (telefone_formatar), para o valor exibido bater com o
    // que será armazenado.
    var saida = '(' + ddd + ') ';
    if (d.length === 11) {
      saida += resto.substring(0, 5);
      if (resto.length > 5) {
        saida += '-' + resto.substring(5, 9);
      }
    } else {
      saida += resto.substring(0, 4);
      if (resto.length > 4) {
        saida += '-' + resto.substring(4, 8);
      }
    }
    return saida;
  }

  function validarTelefone(valor) {
    var d = digitos(valor);
    if (d.length === 10) {
      return /^[1-9][1-9][1-9]\d{7}$/.test(d);
    }
    if (d.length === 11) {
      return /^[1-9][1-9]9\d{8}$/.test(d);
    }
    return false;
  }

  /* ---------- CPF ---------- */

  function validarCpf(valor) {
    var d = digitos(valor);
    if (d.length !== 11) {
      return false;
    }
    if (/^(\d)\1{10}$/.test(d)) {
      return false;
    }
    var soma = 0;
    var i;
    for (i = 0; i < 9; i++) {
      soma += parseInt(d.charAt(i), 10) * (10 - i);
    }
    var resto = soma % 11;
    var dv1 = resto < 2 ? 0 : 11 - resto;
    if (dv1 !== parseInt(d.charAt(9), 10)) {
      return false;
    }
    soma = 0;
    for (i = 0; i < 10; i++) {
      soma += parseInt(d.charAt(i), 10) * (11 - i);
    }
    resto = soma % 11;
    var dv2 = resto < 2 ? 0 : 11 - resto;
    return dv2 === parseInt(d.charAt(10), 10);
  }

  function formatarCpf(d) {
    if (!d) {
      return '';
    }
    var saida = d.substring(0, 3);
    if (d.length > 3) {
      saida += '.' + d.substring(3, 6);
    }
    if (d.length > 6) {
      saida += '.' + d.substring(6, 9);
    }
    if (d.length > 9) {
      saida += '-' + d.substring(9, 11);
    }
    return saida;
  }

  /* ---------- CEP ---------- */

  function formatarCep(d) {
    if (!d) {
      return '';
    }
    var saida = d.substring(0, 5);
    if (d.length > 5) {
      saida += '-' + d.substring(5, 8);
    }
    return saida;
  }

  function limite(d, max) {
    return d.length > max ? d.substring(0, max) : d;
  }

  /* ---------- Infraestrutura da máscara ---------- */

  /**
   * Instala digitação/colagem/apagagem seguras em um campo.
   *
   * opcoes: {
   *   normalizar: function(digitos) -> digitos,   // regras do tipo DDI/limite
   *   formatar:   function(digitos) -> texto,
   *   validar:    function(valorFormatado) -> bool (opcional),
   *   mensagemErro: string (opcional, usada com validar)
   * }
   */
  function instalarMascara(campo, opcoes) {
    if (!campo || typeof campo.addEventListener !== 'function') {
      return;
    }
    var normalizar = opcoes.normalizar;
    var formatar = opcoes.formatar;
    var validar = opcoes.validar || null;
    var mensagemErro = opcoes.mensagemErro || '';

    function aplicar(bruto, digitosAntes) {
      var d = normalizar(digitos(bruto));
      campo.value = formatar(d);

      if (validar) {
        if (campo.setCustomValidity) {
          campo.setCustomValidity(d === '' || validar(campo.value) ? '' : mensagemErro);
        }
      }

      // Cursor: logo após a mesma quantidade de dígitos de antes da edição.
      var pos = 0;
      var contados = 0;
      while (pos < campo.value.length && contados < digitosAntes) {
        var atual = campo.value.charAt(pos);
        if (atual >= '0' && atual <= '9') {
          contados++;
        }
        pos++;
      }
      if (digitosAntes > 0 && contados < digitosAntes) {
        // Perdeu dígitos (ex.: DDI removido) — manda para o fim.
        pos = campo.value.length;
      }
      if (digitosAntes >= digitos(campo.value).length) {
        // Cursor já estava no fim: fica logo após a última pontuação.
        pos = campo.value.length;
      }
      if (campo.setSelectionRange) {
        try {
          campo.setSelectionRange(pos, pos);
        } catch (erro) {}
      }
    }

    // Digitação (o navegador insere/apaga; aqui só refazemos a máscara).
    campo.addEventListener('input', function () {
      var pos = campo.selectionStart;
      var antes = digitos(campo.value.substring(0, pos)).length;
      aplicar(campo.value, antes);
    });

    // Backspace/Delete: apagam sempre um DÍGITO na posição do cursor,
    // mesmo quando o cursor está ao lado de pontuação gerada pela máscara.
    campo.addEventListener('keydown', function (evento) {
      var tecla = evento.key;
      var backspace = tecla === 'Backspace' || evento.keyCode === 8;
      var deleteParaFrente = tecla === 'Delete' || evento.keyCode === 46;
      if (!backspace && !deleteParaFrente) {
        return;
      }
      if (campo.selectionStart !== campo.selectionEnd) {
        return; // seleção múltipla: deixa o navegador tratar
      }
      var valor = campo.value;
      var pos = campo.selectionStart;
      var d = digitos(valor);
      var antes = digitos(valor.substring(0, pos)).length;

      if (backspace) {
        if (antes === 0) {
          return; // nada de dígito antes do cursor
        }
        evento.preventDefault();
        d = d.substring(0, antes - 1) + d.substring(antes);
        aplicar(d, antes - 1);
      } else {
        if (antes >= d.length) {
          return;
        }
        evento.preventDefault();
        d = d.substring(0, antes) + d.substring(antes + 1);
        aplicar(d, antes);
      }
    });

    // Colagem: intercepta para normalizar o texto colado (DDI, pontuação…).
    campo.addEventListener('paste', function (evento) {
      var fonte = evento.clipboardData || global.clipboardData;
      if (!fonte || typeof fonte.getData !== 'function') {
        return; // sem API: deixa o navegador colar; o 'input' formata
      }
      evento.preventDefault();
      var texto = fonte.getData('text') || '';
      var inicio = campo.selectionStart;
      var fim = campo.selectionEnd;
      var valor = campo.value;
      var antes = digitos(valor.substring(0, inicio)).length + digitos(texto).length;
      aplicar(valor.substring(0, inicio) + texto + valor.substring(fim), antes);
    });
  }

  var API = {
    digitos: digitos,
    normalizarTelefone: normalizarTelefone,
    formatarTelefone: formatarTelefone,
    validarTelefone: validarTelefone,
    validarCpf: validarCpf,
    formatarCpf: formatarCpf,
    formatarCep: formatarCep,
    instalarMascara: instalarMascara,

    telefone: function (campo) {
      instalarMascara(campo, {
        normalizar: normalizarTelefone,
        formatar: formatarTelefone,
        validar: validarTelefone,
        mensagemErro: 'Telefone incompleto ou inválido. Use o DDD + número, ex.: (11) 1234-5678 ou (11) 91234-5678.'
      });
    },

    cpf: function (campo) {
      instalarMascara(campo, {
        normalizar: function (d) { return limite(d, 11); },
        formatar: formatarCpf,
        validar: validarCpf,
        mensagemErro: 'CPF inválido. Confira os 11 números, incluindo os dois dígitos verificadores.'
      });
    },

    cep: function (campo) {
      instalarMascara(campo, {
        normalizar: function (d) { return limite(d, 8); },
        formatar: formatarCep
      });
    }
  };

  if (typeof module === 'object' && module && module.exports) {
    module.exports = API;
  }
  global.Mascaras = API;
}(typeof window !== 'undefined' ? window : global));
