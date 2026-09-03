<?php
/**
 * ExcluirFreelancer.php
 *
 * Exclui um perfil de freelancer do banco e apaga o arquivo de
 * imagem correspondente do servidor. A confirmação ("Tem certeza?")
 * é feita em JavaScript, no botão da tela CRUD_Freelancers.php.
 *
 * Só aceita a exclusão via POST.
 */
session_start();
require_once dirname(__FILE__) . '/../config/conexao.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_freelancers.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuarioId = (int) $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_freelancer']) || !ctype_digit((string) $_POST['id_freelancer'])) {
    header('Location: CRUD_Freelancers.php');
    exit;
}

$idFreelancer = (int) $_POST['id_freelancer'];

// Busca o nome do arquivo de imagem antes de apagar o registro,
// e garante que o freelancer pertence ao usuário logado.
$stmt = $conexao->prepare("SELECT portfolio FROM freelancers WHERE id_freelancer = ? AND usuario_id = ?");
$stmt->bind_param('ii', $idFreelancer, $usuarioId);
$stmt->execute();
$resultado = $stmt->get_result();
$freelancer = $resultado->fetch_assoc();
$stmt->close();

if ($freelancer) {
    $stmtDelete = $conexao->prepare("DELETE FROM freelancers WHERE id_freelancer = ? AND usuario_id = ?");
    $stmtDelete->bind_param('ii', $idFreelancer, $usuarioId);

    if ($stmtDelete->execute()) {
        excluir_imagem_freelancer($freelancer['portfolio']);
    }

    $stmtDelete->close();
}

header('Location: CRUD_Freelancers.php');
exit;