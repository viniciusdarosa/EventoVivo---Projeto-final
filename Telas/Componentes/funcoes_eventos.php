<?php
/* ==========================================================
 * COMPONENTE: funcoes_eventos.php
 * Biblioteca de funções auxiliares para categorias, consultas, validações, uploads e operações relacionadas aos eventos.
 * ========================================================== */

/**
 * funcoes_eventos.php
 * Funções auxiliares usadas pelas telas de CRUD de Eventos. 
 * Centralizar essa lógica aqui evita repetir
 * o mesmo código de validação/upload em cada tela.
 *
 * Todas as funções recebem $conexao (objeto mysqli) já aberto por
 * config/conexao.php.
 */

// Pasta física onde as imagens de capa dos eventos são salvas.
// dirname(__FILE__) . '/../../' vai de Telas/Componentes/ até a raiz do projeto.
define('EVENTOS_UPLOAD_DIR', dirname(__FILE__) . '/../../uploads/eventos/');
// Tamanho máximo aceito para a imagem de capa (2MB).
define('EVENTOS_UPLOAD_MAX_BYTES', 2 * 1024 * 1024);
/**
 * Busca todas as categorias de eventos, para popular o <select>
 * do formulário de cadastro/edição.
 *
 * @param mysqli $conexao
 * @return array lista de arrays associativos (id_categoria, nome)
 */
function buscar_categorias_eventos($conexao) {
    $categorias = array();

    $sql = "SELECT id_categoria, nome FROM categorias_eventos ORDER BY nome ASC";
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
 * Retorna a URL/caminho de exibição da imagem de capa de um evento.
 * Se o evento não tiver imagem cadastrada, devolve um placeholder
 *
 * @param string $imagemCapa nome do arquivo salvo em uploads/eventos/
 * @return string caminho (relativo às páginas em Telas/)
 */
function evento_imagem_src($imagemCapa) {
    if (empty($imagemCapa)) {
        // Placeholder simples em SVG, no mesmo espírito do favicon
        return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 240"%3E'
             . '%3Crect width="400" height="240" fill="%231e1a12"/%3E'
             . '%3Ctext x="200" y="128" font-family="Arial, sans-serif" font-weight="700" '
             . 'font-size="20" fill="%23a89d80" text-anchor="middle"%3ESem imagem%3C/text%3E'
             . '%3C/svg%3E';
    }

    return '../uploads/eventos/' . rawurlencode($imagemCapa);
}

/**
 * Valida o arquivo enviado em $_FILES['imagem_capa'] antes de salvar.
 *
 * Regras:
 *  - campo é opcional (usuário pode não enviar nada — quem decide se
 *    isso é erro ou não é a tela que chama esta função);
 *  - só aceita image/jpeg, image/png, image/gif;
 *  - valida tanto o tipo enviado pelo navegador quanto a extensão
 *    real do nome do arquivo, porque o tipo do $_FILES pode ser
 *    forjado pelo cliente;
 *  - limite de 2MB.
 *
 * @param array $arquivo elemento de $_FILES, ex: $_FILES['imagem_capa']
 * @return array array('ok' => bool, 'erro' => string|null, 'extensao' => string|null)
 */
function validar_upload_imagem_evento($arquivo) {
    // Nenhum arquivo enviado.
    if (!isset($arquivo) || $arquivo['error'] === UPLOAD_ERR_NO_FILE) {
        return array('ok' => true, 'enviado' => false, 'erro' => null, 'extensao' => null);
    }

    // Algum erro de upload do próprio PHP.
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        return array('ok' => false, 'enviado' => true, 'erro' => 'Falha ao enviar o arquivo (código ' . $arquivo['error'] . ').', 'extensao' => null);
    }

    // Tamanho.
    if ($arquivo['size'] > EVENTOS_UPLOAD_MAX_BYTES) {
        return array('ok' => false, 'enviado' => true, 'erro' => 'A imagem deve ter no máximo 2MB.', 'extensao' => null);
    }

    // Tipos aceitos: MIME (informado pelo navegador) + extensão real do nome do arquivo.
    $tiposAceitos = array('image/jpeg', 'image/png', 'image/gif');
    $extensoesAceitas = array('jpg', 'jpeg', 'png', 'gif');

    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

    $tipoOk = in_array($arquivo['type'], $tiposAceitos, true);
    $extensaoOk = in_array($extensao, $extensoesAceitas, true);

    if (!$tipoOk || !$extensaoOk) {
        return array('ok' => false, 'enviado' => true, 'erro' => 'Formato de imagem inválido. Envie JPG, PNG ou GIF.', 'extensao' => null);
    }

    // Valida o MIME real do conteúdo (não o $_FILES['type'], que o cliente
    // forja). Usa finfo quando disponível; getimagesize() reforça.
    $mimeReal = null;
    if (class_exists('finfo')) {
        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mimeReal = @finfo_file($finfo, $arquivo['tmp_name']);
            finfo_close($finfo);
        }
    }
    if ($mimeReal !== null && !in_array($mimeReal, $tiposAceitos, true)) {
        return array('ok' => false, 'enviado' => true, 'erro' => 'O conteúdo do arquivo não é uma imagem JPG, PNG ou GIF.', 'extensao' => null);
    }

    // Confere também os "magic bytes" do arquivo, pra não confiar só
    // no type/extensão (que o cliente pode forjar). getimagesize()
    // só retorna algo != false se o conteúdo for mesmo uma imagem.
    $infoImagem = @getimagesize($arquivo['tmp_name']);
    if ($infoImagem === false) {
        return array('ok' => false, 'enviado' => true, 'erro' => 'O arquivo enviado não é uma imagem válida.', 'extensao' => null);
    }

    // Reforço: se finfo informou o tipo e ele é de imagem, precisa ser um dos aceitos.
    if ($mimeReal === null && isset($infoImagem['mime']) && !in_array($infoImagem['mime'], $tiposAceitos, true)) {
        return array('ok' => false, 'enviado' => true, 'erro' => 'O conteúdo do arquivo não é uma imagem JPG, PNG ou GIF.', 'extensao' => null);
    }

    return array('ok' => true, 'enviado' => true, 'erro' => null, 'extensao' => $extensao);
}

/**
 * Move o arquivo de upload já validado para a pasta de eventos,
 * com um nome aleatório (imprevisível), sem usar o nome original.
 *
 * @param array  $arquivo   elemento de $_FILES['imagem_capa']
 * @param string $extensao  extensão validada (retornada por validar_upload_imagem_evento)
 * @return string|false nome do arquivo salvo, ou false em caso de falha
 */
function salvar_upload_imagem_evento($arquivo, $extensao) {
    if (!is_dir(EVENTOS_UPLOAD_DIR)) {
        // Cria a pasta se por algum motivo ela não existir.
        @mkdir(EVENTOS_UPLOAD_DIR, 0755, true);
    }

    $nomeArquivo = 'evento_' . bin2hex(ev_random_bytes(16)) . '.' . $extensao;
    $caminhoDestino = EVENTOS_UPLOAD_DIR . $nomeArquivo;

    if (move_uploaded_file($arquivo['tmp_name'], $caminhoDestino)) {
        return $nomeArquivo;
    }

    return false;
}

/**
 * Apaga do servidor o arquivo de imagem de um evento, se existir.
 * Usado ao trocar a imagem na edição e ao excluir o evento.
 *
 * @param string $imagemCapa nome do arquivo salvo em uploads/eventos/
 */
function excluir_imagem_evento($imagemCapa) {
    if (empty($imagemCapa)) {
        return;
    }

    $caminho = EVENTOS_UPLOAD_DIR . $imagemCapa;

    if (is_file($caminho)) {
        @unlink($caminho);
    }
}


/**
 * Marca como arquivados os eventos encerrados há mais de 1 mês.
 *
 * A rotina roda quando a página Eventos.php é aberta, mas apenas
 * ALTERA o registro (eventos.arquivado = 1): nada é apagado do
 * banco nem do servidor. Eventos arquivados somem das listagens
 * públicas e podem ser restaurados pelo dono em CRUD_Eventos.php.
 *
 * @param mysqli $conexao conexão já aberta
 */
function arquivar_eventos_encerrados($conexao) {
    $stmt = $conexao->prepare(
        "UPDATE eventos
            SET arquivado = 1
          WHERE arquivado = 0
            AND data_fim_evento < DATE_SUB(CURDATE(), INTERVAL 1 MONTH)"
    );

    if ($stmt === false) {
        // Não interrompe a página de eventos caso a rotina falhe.
        return;
    }

    $stmt->execute();
    $stmt->close();
}

/**
 * Formata um valor decimal para exibição
 * em Real. Se o valor for 0, mostra "Gratuito".
 */
function formatar_valor_evento($valor) {
    $valor = (float) $valor;

    if ($valor <= 0) {
        return 'Gratuito';
    }

    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Formata data no padrão brasileiro (dd/mm/aaaa) a partir do
 * formato do MySQL (aaaa-mm-dd). Retorna string vazia se a data
 * estiver vazia/nula.
 */
function formatar_data_evento($data) {
    if (empty($data) || $data === '0000-00-00') {
        return '';
    }

    $timestamp = strtotime($data);
    return date('d/m/Y', $timestamp);
}

/**
 * Gera hash bcrypt da senha para armazenamento no banco.
 * Usa password_hash() (PHP 5.5+) ou o equivalente baseado em crypt()
 * definido no bootstrap para PHP 5.3 — ambos geram $2y$ (Blowfish).
 *
 * @param string $senha Senha em texto puro
 * @return string Hash bcrypt (60 caracteres, formato $2y$10$...)
 */
function hash_senha($senha) {
    $hash = password_hash($senha, PASSWORD_BCRYPT);
    if ($hash === false) {
        // Só acontece se o Blowfish do crypt() estiver indisponível.
        // Fallback legado para não travar o cadastro (migra no próximo login).
        $salt = bin2hex(ev_random_bytes(16));
        return 'sha256:' . $salt . ':' . hash('sha256', $salt . $senha . $salt);
    }
    return $hash;
}

/**
 * Verifica se a senha confere com o hash armazenado.
 * Suporta três formatos:
 *   - Atual:  bcrypt $2y$... (password_hash)
 *   - Legado: sha256:$salt:$hash (formato anterior — migrado no login)
 *   - Legado: texto puro (cadastro antigo — migrado no login)
 * Nenhum formato legado é usado em novos cadastros.
 *
 * @param string $senha Senha em texto puro fornecida no login
 * @param string $hashArmazenado Hash do banco
 * @return bool True se a senha estiver correta
 */
function verificar_senha($senha, $hashArmazenado) {
    if (!is_string($senha) || !is_string($hashArmazenado) || $hashArmazenado === '') {
        return false;
    }

    // Formato atual: bcrypt (gerado por password_hash)
    if (strpos($hashArmazenado, '$2y$') === 0 || strpos($hashArmazenado, '$2a$') === 0) {
        return password_verify($senha, $hashArmazenado);
    }

    // Formato legado: sha256:$salt:$hash
    if (strpos($hashArmazenado, 'sha256:') === 0) {
        $partes = explode(':', $hashArmazenado);
        if (count($partes) !== 3) {
            return false;
        }
        $salt = $partes[1];
        $hashEsperado = $partes[2];
        $hashCalculado = hash('sha256', $salt . $senha . $salt);
        return hash_equals($hashCalculado, $hashEsperado);
    }

    // Formato mais antigo: texto puro (migração única no primeiro login)
    return hash_equals($hashArmazenado, $senha);
}

/**
 * Indica se o hash armazenado precisa ser migrado para bcrypt
 * (usado pelo login para atualizar senhas legadas).
 *
 * @param string $hashArmazenado
 * @return bool
 */
function senha_requer_atualizacao($hashArmazenado) {
    if (!is_string($hashArmazenado) || $hashArmazenado === '') {
        return true;
    }
    if (strpos($hashArmazenado, '$2y$') !== 0 && strpos($hashArmazenado, '$2a$') !== 0) {
        return true;
    }
    return password_needs_rehash($hashArmazenado, PASSWORD_BCRYPT);
}
