<?php
/**
 * funcoes_eventos.php
 *
 * Funções auxiliares usadas pelas telas de CRUD de Eventos
 * (CadastrarEvento.php, EditarEvento.php, CRUD_Eventos.php,
 * ExcluirEvento.php). Centralizar essa lógica aqui evita repetir
 * o mesmo código de validação/upload em cada tela.
 *
 * Todas as funções recebem $conexao (objeto mysqli) já aberto por
 * config/conexao.php.
 */

// Pasta física onde as imagens de capa dos eventos são salvas.
// dirname(__FILE__) . '/../../' sobe de Telas/Componentes/ até a raiz do projeto.
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
 * (SVG embutido em data URI, então não depende de nenhum arquivo
 * extra no projeto).
 *
 * @param string $imagemCapa nome do arquivo salvo em uploads/eventos/
 * @return string caminho (relativo às páginas em Telas/) ou data URI
 */
function evento_imagem_src($imagemCapa) {
    if (empty($imagemCapa)) {
        // Placeholder simples em SVG, no mesmo espírito do favicon
        // inline já usado em Componentes/header.php.
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
 *  - valida tanto o "type" enviado pelo navegador quanto a extensão
 *    real do nome do arquivo, porque o "type" do $_FILES pode ser
 *    forjado pelo cliente;
 *  - limite de 2MB.
 *
 * @param array $arquivo elemento de $_FILES, ex: $_FILES['imagem_capa']
 * @return array array('ok' => bool, 'erro' => string|null, 'extensao' => string|null)
 */
function validar_upload_imagem_evento($arquivo) {
    // Nenhum arquivo enviado (campo opcional, ex: na edição).
    if (!isset($arquivo) || $arquivo['error'] === UPLOAD_ERR_NO_FILE) {
        return array('ok' => true, 'enviado' => false, 'erro' => null, 'extensao' => null);
    }

    // Algum erro de upload do próprio PHP (ex: excedeu upload_max_filesize).
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

    $tipoOk = in_array($arquivo['type'], $tiposAceitos);
    $extensaoOk = in_array($extensao, $extensoesAceitas);

    if (!$tipoOk || !$extensaoOk) {
        return array('ok' => false, 'enviado' => true, 'erro' => 'Formato de imagem inválido. Envie JPG, PNG ou GIF.', 'extensao' => null);
    }

    // Confere também os "magic bytes" do arquivo, pra não confiar só
    // no type/extensão (que o cliente pode forjar). getimagesize()
    // só retorna algo != false se o conteúdo for mesmo uma imagem.
    $infoImagem = @getimagesize($arquivo['tmp_name']);
    if ($infoImagem === false) {
        return array('ok' => false, 'enviado' => true, 'erro' => 'O arquivo enviado não é uma imagem válida.', 'extensao' => null);
    }

    return array('ok' => true, 'enviado' => true, 'erro' => null, 'extensao' => $extensao);
}

/**
 * Move o arquivo de upload já validado para a pasta de eventos,
 * com um nome novo (evita colisão de nomes e path traversal — nunca
 * usamos o nome original enviado pelo usuário como nome final).
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

    $nomeArquivo = uniqid('evento_') . '.' . $extensao;
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
 * Formata um valor decimal (vindo do banco, ex: "0.00") para exibição
 * em Real, ex: "R$ 25,00". Se o valor for 0, mostra "Gratuito".
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
 * Gera um salt aleatório seguro para hash de senha.
 * PHP 5.3.9 compatível: usa openssl_random_pseudo_bytes se disponível,
 * senão fallback com mt_rand + uniqid.
 *
 * @param int $length Tamanho do salt em bytes (padrão 32)
 * @return string Salt em hexadecimal
 */
function gerar_salt($length = 32) {
    if (function_exists('openssl_random_pseudo_bytes')) {
        $bytes = openssl_random_pseudo_bytes($length, $crypto_strong);
        if ($crypto_strong) {
            return bin2hex($bytes);
        }
    }
    // Fallback para PHP 5.3.9 sem openssl ou se não for cryptographically strong
    $salt = '';
    for ($i = 0; $i < $length; $i++) {
        $salt .= sprintf('%02x', mt_rand(0, 255));
    }
    $salt .= uniqid('', true);
    return substr($salt, 0, $length * 2);
}

/**
 * Gera hash SHA-256 da senha com salt.
 * Formato retornado: sha256:$salt:$hash (para armazenar no banco)
 *
 * @param string $senha Senha em texto puro
 * @param string $salt Salt em hexadecimal (se vazio, gera novo)
 * @return string Hash no formato sha256:$salt:$hash
 */
function hash_senha($senha, $salt = '') {
    if ($salt === '') {
        $salt = gerar_salt();
    }
    $hash = hash('sha256', $salt . $senha . $salt);
    return 'sha256:' . $salt . ':' . $hash;
}

/**
 * Verifica se a senha confere com o hash armazenado.
 * Suporta dois formatos:
 *   - Novo: sha256:$salt:$hash (seguro, com salt)
 *   - Legado: texto puro (para compatibilidade com usuários existentes)
 *
 * @param string $senha Senha em texto puro fornecida no login
 * @param string $hashArmazenado Hash do banco (formato sha256:$salt:$hash) ou texto puro
 * @return bool True se a senha estiver correta
 */
function verificar_senha($senha, $hashArmazenado) {
    if (empty($hashArmazenado)) {
        return false;
    }

    // Formato novo: sha256:$salt:$hash
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

    // Formato legado: comparação direta (texto puro)
    // Útil para usuários cadastrados antes do sistema de hash
    return $senha === $hashArmazenado;
}

/**
 * Comparação de strings em tempo constante (evita timing attacks).
 * PHP 5.6+ tem hash_equals nativo; esta é compatível com 5.3.9.
 */
if (!function_exists('hash_equals')) {
    function hash_equals($known_string, $user_string) {
        if (strlen($known_string) !== strlen($user_string)) {
            return false;
        }
        $result = 0;
        for ($i = 0; $i < strlen($known_string); $i++) {
            $result |= (ord($known_string[$i]) ^ ord($user_string[$i]));
        }
        return $result === 0;
    }
}
