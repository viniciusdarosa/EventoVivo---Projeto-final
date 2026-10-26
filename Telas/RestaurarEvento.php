<?php
/* ==========================================================
 * COMPONENTE: RestaurarEvento.php
 * Processa a restauração de um evento arquivado do usuário autenticado.
 * ========================================================== */

/**
 * RestaurarEvento.php
 *
 * Desfaz o arquivamento (eventos.arquivado = 0), devolvendo o
 * evento às listagens públicas. É o oposto de ExcluirEvento.php.
 *
 * Só aceita a ação via POST, para reduzir o risco de restauração
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
    flash_definir('Evento inválido para restauração.', 'erro');
    redirecionar('CRUD_Eventos.php');
}

// Restaura apenas se o evento pertence ao usuário logado.
$stmt = $conexao->prepare("UPDATE eventos SET arquivado = 0 WHERE id_evento = ? AND usuario_id = ?");
if ($stmt === false) {
    registrar_log('Falha ao preparar restauração: ' . $conexao->error, 'ERROR');
    flash_definir('Não foi possível restaurar o evento agora. Tente novamente.', 'erro');
    redirecionar('CRUD_Eventos.php');
}
$stmt->bind_param('ii', $idEvento, $usuarioId);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $_SESSION['flash_eventos'] = 'Evento restaurado. Ele volta a aparecer nas listagens públicas.';
} else {
    flash_definir('Não foi possível restaurar: evento não encontrado ou sem permissão.', 'erro');
}

$stmt->close();

redirecionar('CRUD_Eventos.php');
