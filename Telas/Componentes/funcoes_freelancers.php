<?php
/**
 * funcoes_freelancers.php
 *
 * Funções auxiliares usadas pelas telas de CRUD de Freelancers
 * (CadastrarFreelancer.php, EditarFreelancer.php, CRUD_Freelancers.php,
 * ExcluirFreelancer.php, PerfilFreelancer.php).
 *
 * Todas as funções recebem $conexao (objeto mysqli) já aberto por
 * config/conexao.php.
 */

// Pasta física onde as imagens de portfolio dos freelancers são salvas.
define('FREELANCERS_UPLOAD_DIR', dirname(__FILE__) . '/../../uploads/freelancers/');

// Tamanho máximo aceito para imagens (2MB).
define('FREELANCERS_UPLOAD_MAX_BYTES', 2 * 1024 * 1024);

/**
 * Busca todas as categorias de serviços, para popular o <select>
 * do formulário de cadastro/edição.
 *
 * @param mysqli $conexao
 * @return array lista de arrays associativos (id_categoria, nome)
 */
function buscar_categorias_servicos($conexao) {
    $categorias = array();

    $sql = "SELECT id_categoria, nome FROM categorias_servicos ORDER BY nome ASC";
    $resultado = $conexao->query($sql);

    if ($resultado) {
        while ($linha = $resultado->fetch_assoc()) {
            $categorias[] = $linha;
        }
        $resultado->free();
    }

    return $categorias;
}

/**
 * Busca o perfil de freelancer de um usuário específico.
 *
 * @param mysqli $conexao
 * @param int $usuarioId
 * @return array|false Dados do freelancer ou false se não existir
 */
function buscar_freelancer_por_usuario($conexao, $usuarioId) {
    $stmt = $conexao->prepare("
        SELECT f.*, cs.nome AS categoria_nome
        FROM freelancers f
        LEFT JOIN categorias_servicos cs ON cs.id_categoria = f.categoria_id
        WHERE f.usuario_id = ?
        LIMIT 1
    ");
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $freelancer = $resultado->fetch_assoc();
    $stmt->close();

    return $freelancer ?: false;
}

/**
 * Busca um freelancer pelo ID (para perfil público).
 *
 * @param mysqli $conexao
 * @param int $idFreelancer
 * @return array|false Dados do freelancer + dados do usuário ou false
 */
function buscar_freelancer_completo($conexao, $idFreelancer) {
    $stmt = $conexao->prepare("
        SELECT f.*, u.nome, u.foto_perfil, u.biografia, u.cidade, u.estado, u.email, u.telefone,
               cs.nome AS categoria_nome
        FROM freelancers f
        LEFT JOIN usuario u ON u.id_usuario = f.usuario_id
        LEFT JOIN categorias_servicos cs ON cs.id_categoria = f.categoria_id
        WHERE f.id_freelancer = ?
        LIMIT 1
    ");
    $stmt->bind_param('i', $idFreelancer);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $freelancer = $resultado->fetch_assoc();
    $stmt->close();

    return $freelancer ?: false;
}

/**
 * Busca itens do portfolio de um freelancer.
 *
 * @param mysqli $conexao
 * @param int $freelancerId
 * @return array
 */
function buscar_portfolio_freelancer($conexao, $freelancerId) {
    $itens = array();
    $stmt = $conexao->prepare("SELECT * FROM portfolio WHERE freelancer_id = ? ORDER BY id ASC");
    $stmt->bind_param('i', $freelancerId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($linha = $resultado->fetch_assoc()) {
        $itens[] = $linha;
    }
    $stmt->close();
    return $itens;
}

/**
 * Busca avaliações de um freelancer.
 *
 * @param mysqli $conexao
 * @param int $freelancerId
 * @return array
 */
function buscar_avaliacoes_freelancer($conexao, $freelancerId) {
    $avaliacoes = array();
    $stmt = $conexao->prepare("
        SELECT a.*, u.nome AS avaliador_nome, u.foto_perfil AS avaliador_foto
        FROM avaliacoes a
        LEFT JOIN usuario u ON u.id_usuario = a.avaliador
        WHERE a.freelancer_id = ?
        ORDER BY a.data_avaliacao DESC
    ");
    $stmt->bind_param('i', $freelancerId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($linha = $resultado->fetch_assoc()) {
        $avaliacoes[] = $linha;
    }
    $stmt->close();
    return $avaliacoes;
}

/**
 * Calcula a média das avaliações de um freelancer.
 *
 * @param mysqli $conexao
 * @param int $freelancerId
 * @return float|string Média com 1 casa decimal ou '-' se sem avaliações
 */
function calcular_media_avaliacoes($conexao, $freelancerId) {
    $stmt = $conexao->prepare("SELECT AVG(nota) AS media FROM avaliacoes WHERE freelancer_id = ?");
    $stmt->bind_param('i', $freelancerId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $linha = $resultado->fetch_assoc();
    $stmt->close();

    if ($linha && $linha['media'] !== null) {
        return number_format($linha['media'], 1, ',', '.');
    }
    return '-';
}

/**
 * Valida o arquivo enviado em $_FILES antes de salvar.
 *
 * @param array $arquivo elemento de $_FILES
 * @return array array('ok' => bool, 'enviado' => bool, 'erro' => string|null, 'extensao' => string|null)
 */
function validar_upload_imagem_freelancer($arquivo) {
    if (!isset($arquivo) || $arquivo['error'] === UPLOAD_ERR_NO_FILE) {
        return array('ok' => true, 'enviado' => false, 'erro' => null, 'extensao' => null);
    }

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        return array('ok' => false, 'enviado' => true, 'erro' => 'Falha ao enviar o arquivo (código ' . $arquivo['error'] . ').', 'extensao' => null);
    }

    if ($arquivo['size'] > FREELANCERS_UPLOAD_MAX_BYTES) {
        return array('ok' => false, 'enviado' => true, 'erro' => 'A imagem deve ter no máximo 2MB.', 'extensao' => null);
    }

    $tiposAceitos = array('image/jpeg', 'image/png', 'image/gif');
    $extensoesAceitas = array('jpg', 'jpeg', 'png', 'gif');

    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

    $tipoOk = in_array($arquivo['type'], $tiposAceitos);
    $extensaoOk = in_array($extensao, $extensoesAceitas);

    if (!$tipoOk || !$extensaoOk) {
        return array('ok' => false, 'enviado' => true, 'erro' => 'Formato de imagem inválido. Envie JPG, PNG ou GIF.', 'extensao' => null);
    }

    $infoImagem = @getimagesize($arquivo['tmp_name']);
    if ($infoImagem === false) {
        return array('ok' => false, 'enviado' => true, 'erro' => 'O arquivo enviado não é uma imagem válida.', 'extensao' => null);
    }

    return array('ok' => true, 'enviado' => true, 'erro' => null, 'extensao' => $extensao);
}

/**
 * Move o arquivo de upload já validado para a pasta de freelancers.
 *
 * @param array  $arquivo   elemento de $_FILES
 * @param string $extensao  extensão validada
 * @return string|false nome do arquivo salvo, ou false em caso de falha
 */
function salvar_upload_imagem_freelancer($arquivo, $extensao) {
    if (!is_dir(FREELANCERS_UPLOAD_DIR)) {
        @mkdir(FREELANCERS_UPLOAD_DIR, 0755, true);
    }

    $nomeArquivo = uniqid('freelancer_') . '.' . $extensao;
    $caminhoDestino = FREELANCERS_UPLOAD_DIR . $nomeArquivo;

    if (move_uploaded_file($arquivo['tmp_name'], $caminhoDestino)) {
        return $nomeArquivo;
    }

    return false;
}

/**
 * Apaga do servidor o arquivo de imagem de um freelancer, se existir.
 *
 * @param string $imagem nome do arquivo salvo em uploads/freelancers/
 */
function excluir_imagem_freelancer($imagem) {
    if (empty($imagem)) {
        return;
    }

    $caminho = FREELANCERS_UPLOAD_DIR . $imagem;

    if (is_file($caminho)) {
        @unlink($caminho);
    }
}

/**
 * Retorna a URL/caminho de exibição da foto de perfil do freelancer.
 * Se não tiver foto, devolve um placeholder SVG.
 *
 * @param string $fotoPerfil nome do arquivo salvo em uploads/perfil/ (ou null)
 * @return string caminho ou data URI
 */
function freelancer_foto_src($fotoPerfil) {
    if (empty($fotoPerfil)) {
        return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"%3E%3Ccircle cx="32" cy="24" r="12" stroke="%23a89d80" stroke-width="2" fill="none"/%3E%3Cpath d="M8 56c0-13.255 10.745-24 24-24s24 10.745 24 24" stroke="%23a89d80" stroke-width="2" fill="none"/%3E%3C/svg%3E';
    }

    return '../uploads/perfil/' . rawurlencode($fotoPerfil);
}

/**
 * Retorna a URL/caminho de exibição da imagem do portfolio.
 *
 * @param string $imagem nome do arquivo salvo em uploads/freelancers/
 * @return string caminho
 */
function freelancer_portfolio_src($imagem) {
    if (empty($imagem)) {
        return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 240"%3E%3Crect width="400" height="240" fill="%231e1a12"/%3E%3Ctext x="200" y="128" font-family="Arial, sans-serif" font-weight="700" font-size="20" fill="%23a89d80" text-anchor="middle"%3ESem imagem%3C/text%3E%3C/svg%3E';
    }

    return '../uploads/freelancers/' . rawurlencode($imagem);
}

/**
 * Formata um valor decimal para exibição em Real.
 */
function formatar_valor_freelancer($valor) {
    $valor = (float) $valor;

    if ($valor <= 0) {
        return 'A combinar';
    }

    return 'R$ ' . number_format($valor, 2, ',', '.') . '/h';
}

/**
 * Gera estrelas de avaliação (HTML).
 *
 * @param float $media Nota média (ex: 4.5)
 * @return string HTML com estrelas
 */
function render_estrelas_avaliacao($media) {
    if ($media === '-' || $media === '') {
        return '<span class="sem-avaliacao">Sem avaliações</span>';
    }

    $media = (float) $media;
    $html = '<div class="estrelas-avaliacao">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($media)) {
            $html .= '<span class="estrela cheia">★</span>';
        } elseif ($i - 0.5 <= $media) {
            $html .= '<span class="estrela meia">★</span>';
        } else {
            $html .= '<span class="estrela vazia">☆</span>';
        }
    }
    $html .= ' <span class="media-numero">(' . number_format($media, 1, ',', '.') . ')</span>';
    $html .= '</div>';
    return $html;
}