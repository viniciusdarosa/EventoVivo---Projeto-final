<?php
/* ==========================================================
 * COMPONENTE: AvaliarFreelancer.php
 * Tela e processamento responsáveis por registrar uma avaliação de um freelancer, validando o usuário e os dados enviados.
 * ========================================================== */

/**
 * AvaliarFreelancer.php — Processamento de avaliação de freelancer
 */
require_once dirname(__FILE__) . '/../config/bootstrap.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_freelancers.php';

exigir_login('Login.php');

// Avaliação altera dados: somente POST com token CSRF.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('Artistas.php');
}
csrf_exigir();

$avaliadorId = (int) $_SESSION['id_usuario'];
$freelancerId = entrada_inteiro($_POST, 'freelancer_id');
$nota = entrada_inteiro($_POST, 'nota');
$comentario = entrada_texto($_POST, 'comentario');
// Somente destinos internos são aceitos (whitelist no bootstrap).
$redirect = destino_interno_seguro(entrada_texto($_POST, 'redirect'), 'Artistas.php');

if ($freelancerId <= 0 || $nota < 1 || $nota > 5) {
    flash_definir('Avaliação inválida: confira a nota e o artista selecionado.', 'erro');
    redirecionar($redirect);
}

// Limita o comentário para evitar abuso de armazenamento.
if (strlen($comentario) > 2000) {
    $comentario = substr($comentario, 0, 2000);
}

// Verifica se o freelancer existe
$freelancer = buscar_freelancer_completo($conexao, $freelancerId);
if (!$freelancer) {
    flash_definir('Artista não encontrado para avaliação.', 'erro');
    redirecionar($redirect);
}

// Não pode avaliar a si mesmo
if ($freelancer['usuario_id'] == $avaliadorId) {
    flash_definir('Você não pode avaliar o seu próprio perfil.', 'erro');
    redirecionar($redirect);
}

// Verifica se já avaliou (a tabela tem UNIQUE(avaliador, freelancer_id))
$stmt = $conexao->prepare("SELECT id FROM avaliacoes WHERE avaliador = ? AND freelancer_id = ? LIMIT 1");
if ($stmt === false) {
    registrar_log('Falha ao preparar consulta de avaliação: ' . $conexao->error, 'ERROR');
    flash_definir('Não foi possível registrar a avaliação agora. Tente novamente.', 'erro');
    redirecionar($redirect);
}
$stmt->bind_param('ii', $avaliadorId, $freelancerId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    flash_definir('Você já avaliou este artista. Cada conta avalia cada artista uma única vez.', 'erro');
    redirecionar($redirect);
}
$stmt->close();

// Insere a avaliação
$stmt = $conexao->prepare("INSERT INTO avaliacoes (avaliador, freelancer_id, nota, comentario, data_avaliacao) VALUES (?, ?, ?, ?, NOW())");
if ($stmt === false) {
    registrar_log('Falha ao preparar inserção de avaliação: ' . $conexao->error, 'ERROR');
    flash_definir('Não foi possível registrar a avaliação agora. Tente novamente.', 'erro');
    redirecionar($redirect);
}
$stmt->bind_param('iiis', $avaliadorId, $freelancerId, $nota, $comentario);

if ($stmt->execute()) {
    $stmt->close();
    flash_definir('Avaliação registrada. Obrigado pelo seu retorno!');
} else {
    // Corrida entre a consulta e o INSERT (avaliação duplicada) ou falha
    // de banco: mensagem amigável, detalhe só no log.
    registrar_log('Falha ao inserir avaliação: ' . $stmt->error, 'ERROR');
    $stmt->close();
    flash_definir('Não foi possível registrar a avaliação agora. Você já pode ter avaliado este artista.', 'erro');
}

redirecionar($redirect);