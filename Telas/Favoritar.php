<?php
/* ==========================================================
 * COMPONENTE: Favoritar.php
 * Salva/remove um favorito (evento ou perfil de artista) com
 * método POST + token CSRF e redireciona com feedback (PRG).
 * ========================================================== */

/**
 * Favoritar.php — alternância de favoritos
 */
require_once dirname(__FILE__) . '/../config/bootstrap.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_favoritos.php';

exigir_login('Login.php');

// Favorito altera dados: somente POST com token CSRF.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('Home.php');
}
csrf_exigir();

$usuarioId = usuario_id();
$tipo = entrada_texto($_POST, 'tipo');
$id = entrada_inteiro($_POST, 'id');
// Página de origem do botão (Eventos.php, Busca.php, PerfilFreelancer.php...).
$destino = destino_interno_seguro(entrada_texto($_POST, 'redirect'), 'Home.php');

if (!favorito_tipo_valido($tipo) || $id <= 0) {
    flash_definir('Favorito inválido.', 'erro');
    redirecionar($destino);
}

$resultado = favorito_alternar($conexao, $usuarioId, $tipo, $id);

if ($resultado['ok']) {
    if ($resultado['status'] === 'adicionado') {
        flash_definir('Adicionado aos seus favoritos.');
    } else {
        flash_definir('Removido dos seus favoritos.');
    }
} else {
    flash_definir(
        $resultado['erro'] !== '' ? $resultado['erro'] : 'Não foi possível atualizar o favorito agora.',
        'erro'
    );
}

redirecionar($destino);
