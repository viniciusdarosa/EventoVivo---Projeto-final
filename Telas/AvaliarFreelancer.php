<?php
/**
 * AvaliarFreelancer.php — Processamento de avaliação de freelancer
 * Compatível com PHP 5.3.9
 */
session_start();
require_once dirname(__FILE__) . '/../config/conexao.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_freelancers.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: Login.php');
    exit;
}

$avaliadorId = (int) $_SESSION['id_usuario'];
$freelancerId = isset($_POST['freelancer_id']) && ctype_digit($_POST['freelancer_id']) ? (int) $_POST['freelancer_id'] : 0;
$nota = isset($_POST['nota']) && ctype_digit($_POST['nota']) ? (int) $_POST['nota'] : 0;
$comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'Artistas.php';

if ($freelancerId <= 0 || $nota < 1 || $nota > 5) {
    header('Location: ' . $redirect);
    exit;
}

// Verifica se o freelancer existe
$freelancer = buscar_freelancer_completo($conexao, $freelancerId);
if (!$freelancer) {
    header('Location: ' . $redirect);
    exit;
}

// Não pode avaliar a si mesmo
if ($freelancer['usuario_id'] == $avaliadorId) {
    header('Location: ' . $redirect);
    exit;
}

// Verifica se já avaliou
$stmt = $conexao->prepare("SELECT id FROM avaliacoes WHERE avaliador = ? AND freelancer_id = ? LIMIT 1");
$stmt->bind_param('ii', $avaliadorId, $freelancerId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    header('Location: ' . $redirect);
    exit;
}
$stmt->close();

// Insere a avaliação
$stmt = $conexao->prepare("INSERT INTO avaliacoes (avaliador, freelancer_id, nota, comentario, data_avaliacao) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param('iiis', $avaliadorId, $freelancerId, $nota, $comentario);
$stmt->execute();
$stmt->close();

header('Location: ' . $redirect);
exit;