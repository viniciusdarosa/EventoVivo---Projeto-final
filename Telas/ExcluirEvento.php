<?php
/* ==========================================================
 * COMPONENTE: ExcluirEvento.php
 * Processa o ARQUIVAMENTO de um evento pertencente ao usuário autenticado.
 * ========================================================== */

/**
 * ExcluirEvento.php
 *
 * Marca o evento como arquivado (eventos.arquivado = 1) em vez de
 * apagá-lo: ele some das listagens públicas, mas o registro e a
 * imagem de capa permanecem no banco e no servidor, e o dono pode
 * restaurá-lo em CRUD_Eventos.php.
 *
 * A confirmação ("Tem certeza?") é feita em JavaScript no botão da
 * tela antes do POST — ver os componentes que geram o botão.
 *
 * Só aceita a ação via POST, para reduzir o risco de arquivamento
 * acidental.
 */

require_once dirname(__FILE__) . '/../config/bootstrap.php';
require dirname(__FILE__) . '/Componentes/funcoes_eventos.php';

exigir_login('Login.php');
$usuarioId = usuario_id();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('CRUD_Eventos.php');
}
csrf_exigir();

$idEvento = entrada_inteiro($_POST, 'id_evento');
if ($idEvento <= 0) {
    flash_definir('Evento inválido para arquivamento.', 'erro');
    redirecionar('CRUD_Eventos.php');
}

// Arquiva apenas se o evento pertence ao usuário logado.
// A imagem de capa NÃO é apagada: o evento pode ser restaurado.
$stmt = $conexao->prepare("UPDATE eventos SET arquivado = 1 WHERE id_evento = ? AND usuario_id = ?");
if ($stmt === false) {
    registrar_log('Falha ao preparar arquivamento: ' . $conexao->error, 'ERROR');
    flash_definir('Não foi possível arquivar o evento agora. Tente novamente.', 'erro');
    redirecionar('CRUD_Eventos.php');
}
$stmt->bind_param('ii', $idEvento, $usuarioId);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $_SESSION['flash_eventos'] = 'Evento arquivado. Ele não aparece mais nas listagens públicas e pode ser restaurado nesta página.';
} else {
    // Evento não encontrado, não pertence ao usuário ou já arquivado.
    flash_definir('Não foi possível arquivar: evento não encontrado ou sem permissão.', 'erro');
}

$stmt->close();

redirecionar('CRUD_Eventos.php');
