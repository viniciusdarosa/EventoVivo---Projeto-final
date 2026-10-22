<?php
/* ==========================================================
 * COMPONENTE: ExcluirFreelancer.php
 * Processa a exclusão segura do perfil de freelancer pertencente ao usuário autenticado e remove as fotos do seu carrossel.
 * ========================================================== */

/**
 * ExcluirFreelancer.php
 *
 * Exclui um perfil de freelancer do banco e apaga do servidor as fotos
 * do carrossel de trabalho dele. A foto de perfil (usuario.foto_perfil)
 * pertence à conta e permanece após a exclusão.
 *
 * A confirmação ("Tem certeza?") é feita em JavaScript, no botão da
 * tela CRUD_Freelancers.php. Só aceita a exclusão via POST.
 */
require_once dirname(__FILE__) . '/../config/bootstrap.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_freelancers.php';

exigir_login('Login.php');
$usuarioId = usuario_id();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('CRUD_Freelancers.php');
}
csrf_exigir();

$idFreelancer = entrada_inteiro($_POST, 'id_freelancer');
if ($idFreelancer <= 0) {
    redirecionar('CRUD_Freelancers.php');
}

// A exclusão via cascata apaga as linhas de carrossel_fotos, então os nomes
// dos arquivos são coletados antes. O WHERE f.usuario_id garante que o
// perfil pertence ao usuário logado.
$fotosCarrossel = array();
$stmt = $conexao->prepare("
    SELECT cf.imagem
    FROM carrossel_fotos cf
    INNER JOIN freelancers f ON f.id_freelancer = cf.freelancer_id
    WHERE cf.freelancer_id = ? AND f.usuario_id = ?
");
if ($stmt === false) {
    registrar_log('Falha ao preparar busca de fotos: ' . $conexao->error, 'ERROR');
    redirecionar('CRUD_Freelancers.php');
}
$stmt->bind_param('ii', $idFreelancer, $usuarioId);
$stmt->execute();
$resultado = $stmt->get_result();
while ($linha = $resultado->fetch_assoc()) {
    if (!empty($linha['imagem'])) {
        $fotosCarrossel[] = $linha['imagem'];
    }
}
$stmt->close();

$stmtDelete = $conexao->prepare("DELETE FROM freelancers WHERE id_freelancer = ? AND usuario_id = ?");
if ($stmtDelete === false) {
    registrar_log('Falha ao preparar exclusão: ' . $conexao->error, 'ERROR');
    redirecionar('CRUD_Freelancers.php');
}
$stmtDelete->bind_param('ii', $idFreelancer, $usuarioId);

if ($stmtDelete->execute()) {
    foreach ($fotosCarrossel as $imagem) {
        excluir_imagem_freelancer($imagem);
    }
} else {
    registrar_log('Falha ao excluir freelancer: ' . $stmtDelete->error, 'ERROR');
}

$stmtDelete->close();

redirecionar('CRUD_Freelancers.php');