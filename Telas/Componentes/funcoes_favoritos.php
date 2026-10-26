<?php
/* ==========================================================
 * COMPONENTE: funcoes_favoritos.php
 * Favoritos do usuário logado: eventos e perfis de artista.
 * A tabela favoritos já existe desde a instalação (evento_id OU
 * freelancer_id preenchido, FKs com CASCADE) — aqui fica a regra
 * de negócio: existência, alternância (salvar/remover) e listagem.
 * ========================================================== */

if (!function_exists('favorito_tipo_valido')) {
    /**
     * Só existem dois tipos de favorito no esquema do banco.
     *
     * @param string $tipo 'evento' ou 'freelancer'
     * @return bool
     */
    function favorito_tipo_valido($tipo)
    {
        return $tipo === 'evento' || $tipo === 'freelancer';
    }
}

if (!function_exists('favorito_mapa_usuario')) {
    /**
     * Mapa de favoritos do usuário logado, consultado UMA vez por
     * requisição (cache estático) — os cards da listagem usam esse
     * mapa para mostrar "Salvar" ou "Salvo" sem 1 consulta por card.
     *
     * @param mysqli $conexao
     * @param int    $usuarioId
     * @return array array('evento' => array(id=>true), 'freelancer' => array(id=>true))
     */
    function favorito_mapa_usuario($conexao, $usuarioId)
    {
        static $mapa = null;

        if ($mapa !== null) {
            return $mapa;
        }

        $mapa = array('evento' => array(), 'freelancer' => array());

        if (!($conexao instanceof mysqli) || $usuarioId <= 0) {
            return $mapa;
        }

        $stmt = $conexao->prepare(
            "SELECT evento_id, freelancer_id FROM favoritos WHERE usuario_id = ?"
        );
        if ($stmt === false) {
            registrar_log('Falha ao preparar mapa de favoritos: ' . $conexao->error, 'ERROR');
            return $mapa;
        }

        $stmt->bind_param('i', $usuarioId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($linha = $resultado->fetch_assoc()) {
            if (!empty($linha['evento_id'])) {
                $mapa['evento'][(int) $linha['evento_id']] = true;
            }
            if (!empty($linha['freelancer_id'])) {
                $mapa['freelancer'][(int) $linha['freelancer_id']] = true;
            }
        }
        $stmt->close();

        return $mapa;
    }
}

if (!function_exists('favorito_esta')) {
    /**
     * O usuário logado já salvou este item?
     *
     * @param mysqli $conexao
     * @param int    $usuarioId
     * @param string $tipo 'evento' ou 'freelancer'
     * @param int    $id   id do evento ou freelancer
     * @return bool
     */
    function favorito_esta($conexao, $usuarioId, $tipo, $id)
    {
        if (!favorito_tipo_valido($tipo) || $usuarioId <= 0 || $id <= 0) {
            return false;
        }
        $mapa = favorito_mapa_usuario($conexao, $usuarioId);
        return isset($mapa[$tipo][$id]);
    }
}

if (!function_exists('favorito_alternar')) {
    /**
     * Salva ou remove um favorito (toggle).
     *
     * O DELETE antes do INSERT também absorve duplicidades antigas
     * (a tabela favoritos não tem chave UNIQUE).
     *
     * @param mysqli $conexao
     * @param int    $usuarioId
     * @param string $tipo 'evento' ou 'freelancer'
     * @param int    $id   id do evento ou freelancer
     * @return array array('ok' => bool, 'status' => 'adicionado'|'removido'|'', 'erro' => string)
     */
    function favorito_alternar($conexao, $usuarioId, $tipo, $id)
    {
        $resultado = array('ok' => false, 'status' => '', 'erro' => '');

        if (!($conexao instanceof mysqli)) {
            $resultado['erro'] = 'Serviço indisponível no momento.';
            return $resultado;
        }
        if (!favorito_tipo_valido($tipo) || $usuarioId <= 0 || $id <= 0) {
            $resultado['erro'] = 'Favorito inválido.';
            return $resultado;
        }

        // O alvo precisa existir (eventos arquivados não entram).
        if ($tipo === 'evento') {
            $stmt = $conexao->prepare("SELECT 1 FROM eventos WHERE id_evento = ? AND arquivado = 0 LIMIT 1");
            $sqlInsert = "INSERT INTO favoritos (usuario_id, evento_id, freelancer_id) VALUES (?, ?, NULL)";
            $sqlDelete = "DELETE FROM favoritos WHERE usuario_id = ? AND evento_id = ?";
        } else {
            $stmt = $conexao->prepare("SELECT 1 FROM freelancers WHERE id_freelancer = ? LIMIT 1");
            $sqlInsert = "INSERT INTO favoritos (usuario_id, evento_id, freelancer_id) VALUES (?, NULL, ?)";
            $sqlDelete = "DELETE FROM favoritos WHERE usuario_id = ? AND freelancer_id = ?";
        }

        if ($stmt === false) {
            $resultado['erro'] = 'Não foi possível concluir agora. Tente novamente.';
            registrar_log('Falha ao preparar verificação de favorito: ' . $conexao->error, 'ERROR');
            return $resultado;
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->store_result();
        $existeAlvo = $stmt->num_rows > 0;
        $stmt->close();

        if (!$existeAlvo) {
            $resultado['erro'] = ($tipo === 'evento')
                ? 'Este evento não está mais disponível.'
                : 'Este perfil de artista não foi encontrado.';
            return $resultado;
        }

        if (favorito_esta($conexao, $usuarioId, $tipo, $id)) {
            // REMOVE
            $stmt = $conexao->prepare($sqlDelete);
            if ($stmt === false) {
                $resultado['erro'] = 'Não foi possível remover agora. Tente novamente.';
                registrar_log('Falha ao preparar remoção de favorito: ' . $conexao->error, 'ERROR');
                return $resultado;
            }
            $stmt->bind_param('ii', $usuarioId, $id);
            if ($stmt->execute()) {
                $stmt->close();
                $resultado['ok'] = true;
                $resultado['status'] = 'removido';
                return $resultado;
            }
            registrar_log('Falha ao remover favorito: ' . $stmt->error, 'ERROR');
            $stmt->close();
            $resultado['erro'] = 'Não foi possível remover agora. Tente novamente.';
            return $resultado;
        }

        // ADICIONA
        $stmt = $conexao->prepare($sqlInsert);
        if ($stmt === false) {
            $resultado['erro'] = 'Não foi possível salvar agora. Tente novamente.';
            registrar_log('Falha ao preparar inserção de favorito: ' . $conexao->error, 'ERROR');
            return $resultado;
        }
        $stmt->bind_param('ii', $usuarioId, $id);
        if ($stmt->execute()) {
            $stmt->close();
            $resultado['ok'] = true;
            $resultado['status'] = 'adicionado';
            return $resultado;
        }
        registrar_log('Falha ao inserir favorito: ' . $stmt->error, 'ERROR');
        $stmt->close();
        $resultado['erro'] = 'Não foi possível salvar agora. Tente novamente.';
        return $resultado;
    }
}

if (!function_exists('favoritos_listar_eventos')) {
    /**
     * Eventos favoritados pelo usuário (só não arquivados), com as
     * mesmas colunas que o EventoCard consome.
     *
     * @param mysqli $conexao
     * @param int    $usuarioId
     * @return array
     */
    function favoritos_listar_eventos($conexao, $usuarioId)
    {
        $eventos = array();

        if (!($conexao instanceof mysqli) || $usuarioId <= 0) {
            return $eventos;
        }

        $sql = "SELECT e.*, c.nome AS categoria_nome, u.nome AS organizador_nome,
                       u.email AS organizador_email, u.telefone AS organizador_telefone
                FROM favoritos fv
                INNER JOIN eventos e ON e.id_evento = fv.evento_id
                LEFT JOIN categorias_eventos c ON c.id_categoria = e.categoria_id
                LEFT JOIN usuario u ON u.id_usuario = e.usuario_id
                WHERE fv.usuario_id = ? AND e.arquivado = 0
                ORDER BY e.data_inicio_evento ASC";

        $stmt = $conexao->prepare($sql);
        if ($stmt === false) {
            registrar_log('Falha ao preparar favoritos de eventos: ' . $conexao->error, 'ERROR');
            return $eventos;
        }

        $stmt->bind_param('i', $usuarioId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($linha = $resultado->fetch_assoc()) {
            $eventos[] = $linha;
        }
        $stmt->close();

        return $eventos;
    }
}

if (!function_exists('favoritos_listar_artistas')) {
    /**
     * Perfis de artista favoritados pelo usuário, com as mesmas
     * colunas da listagem de Artistas.php (inclusive média de notas).
     *
     * @param mysqli $conexao
     * @param int    $usuarioId
     * @return array
     */
    function favoritos_listar_artistas($conexao, $usuarioId)
    {
        $artistas = array();

        if (!($conexao instanceof mysqli) || $usuarioId <= 0) {
            return $artistas;
        }

        $sql = "SELECT f.*, u.nome, u.foto_perfil, u.biografia, u.cidade, u.estado, u.email, u.telefone,
                       cs.nome AS categoria_nome,
                       COALESCE(AVG(a.nota), 0) AS media_avaliacao,
                       COUNT(a.id) AS total_avaliacoes
                FROM favoritos fv
                INNER JOIN freelancers f ON f.id_freelancer = fv.freelancer_id
                LEFT JOIN usuario u ON u.id_usuario = f.usuario_id
                LEFT JOIN categorias_servicos cs ON cs.id_categoria = f.categoria_id
                LEFT JOIN avaliacoes a ON a.freelancer_id = f.id_freelancer
                WHERE fv.usuario_id = ?
                GROUP BY f.id_freelancer, u.id_usuario, cs.id_categoria
                ORDER BY u.nome ASC";

        $stmt = $conexao->prepare($sql);
        if ($stmt === false) {
            registrar_log('Falha ao preparar favoritos de artistas: ' . $conexao->error, 'ERROR');
            return $artistas;
        }

        $stmt->bind_param('i', $usuarioId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($linha = $resultado->fetch_assoc()) {
            $artistas[] = $linha;
        }
        $stmt->close();

        return $artistas;
    }
}

if (!function_exists('render_favorito_form')) {
    /**
     * Botão de favorito (formulário POST + CSRF) reutilizável.
     * Visitante vê link de login; logado vê o toggle Salvar/Salvo.
     *
     * @param string $tipo    'evento' ou 'freelancer'
     * @param int    $id      id do item
     * @param string $destino página interna de retorno (whitelist)
     * @param bool   $salvo   estado atual
     * @return void (imprime HTML)
     */
    function render_favorito_form($tipo, $id, $destino, $salvo)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return;
        }

        if (!isset($_SESSION['id_usuario'])): ?>
          <a class="btn-icone btn-favorito"
             href="Login.php"
             title="Entre para salvar nos seus favoritos">☆ Salvar</a>
        <?php return; endif;

        $tipoSeguro = ($tipo === 'freelancer') ? 'freelancer' : 'evento';
        ?>
        <form method="post" action="Favoritar.php">
          <?php echo csrf_campo(); ?>
          <input type="hidden" name="tipo" value="<?php echo $tipoSeguro; ?>">
          <input type="hidden" name="id" value="<?php echo $id; ?>">
          <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($destino); ?>">
          <button type="submit"
                  class="btn-icone btn-favorito <?php echo $salvo ? 'btn-salvo' : ''; ?>"
                  title="<?php echo $salvo ? 'Remover dos favoritos' : 'Salvar nos favoritos'; ?>">
            <?php echo $salvo ? '♥ Salvo' : '☆ Salvar'; ?>
          </button>
        </form>
        <?php
    }
}
