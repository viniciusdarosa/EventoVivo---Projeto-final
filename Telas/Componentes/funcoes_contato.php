<?php
/* ==========================================================
 * COMPONENTE: funcoes_contato.php
 * Normalização e validação de telefone brasileiro (fixo/celular
 * com DDD), CPF com dígitos verificadores, montagem da URL do
 * WhatsApp e renderização do botão de contato do organizador
 * de evento.
 * ========================================================== */

/**
 * Reduz um valor ao que interessa: apenas dígitos.
 * Rejeita arrays enviados via ?campo[]= (retorna '').
 * Quando o número vem com DDI 55 (12 ou 13 dígitos no total),
 * o DDI é removido: o nacional nunca passa de 11 dígitos.
 *
 * @param string $valor
 * @return string dígitos puros ('' se não for string)
 */
function telefone_digitos($valor) {
    if (!is_string($valor)) {
        return '';
    }

    $digitos = preg_replace('/\D/', '', $valor);

    // DDI 55 colado junto com o nacional (10 dígitos) = 12 ou 13 no total.
    if ((strlen($digitos) === 12 || strlen($digitos) === 13) && strpos($digitos, '55') === 0) {
        $digitos = substr($digitos, 2);
    }

    return $digitos;
}

/**
 * Valida a estrutura de um telefone brasileiro já reduzido a dígitos:
 *   - fixo:    DDD (11-99) + 8 dígitos — ex.: (11) 1234-5678;
 *   - celular: DDD (11-99) + 9 + 8 dígitos — ex.: (11) 91234-5678.
 *
 * @param string $valor telefone em qualquer formato
 * @return bool
 */
function telefone_valido($valor) {
    $digitos = telefone_digitos($valor);

    if (strlen($digitos) === 10) {
        return preg_match('/^[1-9][1-9][1-9][0-9]{7}$/', $digitos) === 1;
    }
    if (strlen($digitos) === 11) {
        return preg_match('/^[1-9][1-9]9[0-9]{8}$/', $digitos) === 1;
    }
    return false;
}

/**
 * Normaliza o telefone para o formato de exibição/armazenamento:
 *   - fixo:    (11) 1234-5678
 *   - celular: (11) 91234-5678
 * Devolve '' quando a estrutura é inválida (nada é gravado errado).
 *
 * @param string $valor
 * @return string
 */
function telefone_formatar($valor) {
    if (!telefone_valido($valor)) {
        return '';
    }

    $digitos = telefone_digitos($valor);
    $ddd = substr($digitos, 0, 2);
    $resto = substr($digitos, 2);

    if (strlen($digitos) === 11) {
        return '(' . $ddd . ') ' . substr($resto, 0, 5) . '-' . substr($resto, 5, 4);
    }
    return '(' . $ddd . ') ' . substr($resto, 0, 4) . '-' . substr($resto, 4, 4);
}

/**
 * Gera o link de WhatsApp (wa.me) com DDI 55 + número puro,
 * sem máscara nem caracteres extras na URL.
 *
 * @param string $valor telefone (com ou sem formatação)
 * @return string URL ('' quando o telefone não é válido)
 */
function whatsapp_url($valor) {
    if (!telefone_valido($valor)) {
        return '';
    }
    return 'https://wa.me/55' . telefone_digitos($valor);
}

/**
 * Reduz o CPF a dígitos (rejeita arrays).
 *
 * @param string $valor
 * @return string
 */
function cpf_digitos($valor) {
    if (!is_string($valor)) {
        return '';
    }
    return preg_replace('/\D/', '', $valor);
}

/**
 * Valida o CPF incluindo os dois dígitos verificadores
 * (algoritmo oficial da Receita) e rejeita sequências repetidas
 * como 111.111.111-11.
 *
 * @param string $valor CPF com ou sem formatação
 * @return bool
 */
function cpf_valido($valor) {
    $digitos = cpf_digitos($valor);

    if (strlen($digitos) !== 11 || !ctype_digit($digitos)) {
        return false;
    }
    if (preg_match('/^([0-9])\1{10}$/', $digitos)) {
        return false;
    }

    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += (int) $digitos[$i] * (10 - $i);
    }
    $resto = $soma % 11;
    $dv1 = ($resto < 2) ? 0 : 11 - $resto;
    if ($dv1 !== (int) $digitos[9]) {
        return false;
    }

    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += (int) $digitos[$i] * (11 - $i);
    }
    $resto = $soma % 11;
    $dv2 = ($resto < 2) ? 0 : 11 - $resto;

    return $dv2 === (int) $digitos[10];
}

/**
 * Formata o CPF como 000.000.000-00 quando tiver 11 dígitos.
 *
 * @param string $valor
 * @return string ('' se não tiver 11 dígitos)
 */
function cpf_formatar($valor) {
    $digitos = cpf_digitos($valor);
    if (strlen($digitos) !== 11) {
        return '';
    }
    return substr($digitos, 0, 3) . '.'
         . substr($digitos, 3, 3) . '.'
         . substr($digitos, 6, 3) . '-'
         . substr($digitos, 9, 2);
}

/**
 * Renderiza o botão/canais de contato do organizador de um evento.
 *
 * Regras:
 *   - visitante (sem sessão)  → link coerente pedindo o login;
 *   - próprio dono do evento  → não renderiza nada (ele já vê
 *     Editar/Arquivar — não faz sentido "contatar a si mesmo");
 *   - autenticado             → abre os canais efetivamente
 *     cadastrados do organizador: WhatsApp (wa.me) e/ou e-mail.
 *     Sem nenhum canal, mostra aviso sem link (não inventa contato).
 *
 * O organizador é sempre o usuário DONO do evento
 * (eventos.usuario_id → usuario.id_usuario); nunca o freelancer.
 *
 * @param int         $idOrganizador  eventos.usuario_id
 * @param string      $nomeOrganizador
 * @param string      $emailOrganizador
 * @param string      $telefoneOrganizador
 */
function render_contato_organizador($idOrganizador, $nomeOrganizador, $emailOrganizador, $telefoneOrganizador)
{
    // Visitante: pede o login de forma coerente (link real, não alert).
    if (!isset($_SESSION['id_usuario'])) {
        ?>
        <a class="btn-icone"
           href="Login.php"
           title="Entre na sua conta para falar com o organizador do evento.">Entrar para contatar</a>
        <?php
        return;
    }

    // O dono do evento não vê canal de contato consigo mesmo.
    if ((int) $_SESSION['id_usuario'] === (int) $idOrganizador) {
        return;
    }

    $whatsapp = whatsapp_url($telefoneOrganizador);
    $temEmail = is_string($emailOrganizador) && trim($emailOrganizador) !== '';

    // Sem nenhum canal cadastrado: avisa, sem inventar contato.
    if ($whatsapp === '' && !$temEmail) {
        ?>
        <span class="btn-icone contato-indisponivel"
              title="Este organizador ainda não cadastrou telefone ou e-mail.">Contatar organizador</span>
        <?php
        return;
    }
    ?>
    <details class="contato-organizador">
      <summary class="btn-icone">Contatar organizador</summary>
      <div class="contato-organizador-menu">
        <p class="contato-organizador-nome">
          Contato: <?php echo htmlspecialchars($nomeOrganizador); ?>
        </p>
        <?php if ($whatsapp !== ''): ?>
          <a class="contato-organizador-link"
             href="<?php echo htmlspecialchars($whatsapp); ?>"
             target="_blank"
             rel="noopener noreferrer">WhatsApp</a>
        <?php endif; ?>
        <?php if ($temEmail): ?>
          <a class="contato-organizador-link"
             href="mailto:<?php echo htmlspecialchars($emailOrganizador); ?>">E-mail</a>
        <?php endif; ?>
      </div>
    </details>
    <?php
}
