<?php
/**
 * ExcluirEvento.php
 *
 * Exclui um evento do banco e apaga o arquivo de imagem
 * correspondente do servidor. A confirmação ("Tem certeza?") é
 * feita em JavaScript, no botão da tela CRUD_Eventos.php, antes
 * do link para esta página ser seguido — ver Componentes que
 * geram o botão de excluir.
 *
 * Só aceita a exclusão via POST (o link em CRUD_Eventos.php envia
 * um formulário, não é um <a href> direto), para reduzir o risco
 * de exclusão acidental via link/crawler.
 */

session_start();
require dirname(__FILE__) . '/../config/conexao.php';
require dirname(__FILE__) . '/Componentes/funcoes_eventos.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuarioId = (int) $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_evento']) || !ctype_digit((string) $_POST['id_evento'])) {
    header('Location: CRUD_Eventos.php');
    exit;
}

$idEvento = (int) $_POST['id_evento'];

// Busca o nome do arquivo de imagem antes de apagar o registro,
// e garante que o evento pertence ao usuário logado.
$stmt = $conexao->prepare("SELECT imagem_capa FROM eventos WHERE id_evento = ? AND usuario_id = ?");
$stmt->bind_param('ii', $idEvento, $usuarioId);
$stmt->execute();
$resultado = $stmt->get_result();
$evento = $resultado->fetch_assoc();
$stmt->close();

if ($evento) {
    $stmtDelete = $conexao->prepare("DELETE FROM eventos WHERE id_evento = ? AND usuario_id = ?");
    $stmtDelete->bind_param('ii', $idEvento, $usuarioId);

    if ($stmtDelete->execute()) {
        // Só apaga o arquivo do servidor depois de confirmar que o
        // registro foi removido do banco.
        excluir_imagem_evento($evento['imagem_capa']);
    }

    $stmtDelete->close();
}

// Evento não encontrado (ou não é do usuário) — simplesmente volta
// para a listagem, sem erro exposto ao usuário.
header('Location: CRUD_Eventos.php');
exit;
