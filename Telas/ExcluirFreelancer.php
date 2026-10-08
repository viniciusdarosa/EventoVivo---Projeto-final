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
$stmtDelete->bind_param('ii', $idFreelancer, $usuarioId);

if ($stmtDelete->execute()) {
    foreach ($fotosCarrossel as $imagem) {
        excluir_imagem_freelancer($imagem);
    }
}

$stmtDelete->close();

header('Location: CRUD_Freelancers.php');
exit;