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

    // basename() garante que só o nome do arquivo seja usado, nunca
    // caminhos com ../ que possam vir armazenados por engano.
    $caminho = EVENTOS_UPLOAD_DIR . basename($imagemCapa);

    if (is_file($caminho)) {
        @unlink($caminho);
    }
}


/**
 * REGRA DE PUBLICAÇÃO DE EVENTOS (validada no servidor).
 *
 * Quem pode CRIAR eventos são as contas de organizador:
 *   - tipo 'empresa' (produtoras/organizadoras);
 *   - tipo 'admin'   (administração do sistema).
 *
 * Contas comuns do tipo 'usuario' podem navegar, avaliar artistas,
 * favoritar e gerenciar os próprios eventos já criados, mas não
 * publicam novos. Esta regra é aplicada aqui (servidor) e refletida
 * na interface (botão "+ Novo Evento" só aparece para quem pode) —
 * esconder o botão nunca é a única barreira.
 *
 * @param mysqli $conexao conexão já aberta
 * @return bool
 */
function usuario_pode_publicar_eventos($conexao) {
    $tipo = isset($_SESSION['tipo_usuario']) ? (string) $_SESSION['tipo_usuario'] : '';

    // Sessão legada (criada antes deste cache existir): consulta o banco.
    if ($tipo === '' && usuario_logado()) {
        $stmt = $conexao->prepare("SELECT tipo FROM usuario WHERE id_usuario = ? LIMIT 1");
        if ($stmt !== false) {
            $id = usuario_id();
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($tipoBanco);
            if ($stmt->fetch()) {
                $tipo = (string) $tipoBanco;
                $_SESSION['tipo_usuario'] = $tipo;
            }
            $stmt->close();
        }
    }

    return ($tipo === 'empresa' || $tipo === 'admin');
}

/**
 * Valida os campos de um evento (cadastro e edição usam as mesmas regras).
 * Devolve a lista de mensagens de erro — vazia quando tudo está válido.
 *
 * Regras: datas reais no formato aaaa-mm-dd, fim >= início, horários
 * reais com fim > início, categoria existente, vagas >= 1, valor >= 0,
 * estado com 2 letras, CEP com 8 dígitos quando informado e limites
 * de tamanho compatíveis com as colunas do banco.
 *
 * @param mysqli $conexao
 * @param array  $dados campos do formulário (após entrada_texto)
 * @return array lista de erros (strings)
 */
function validar_dados_evento($conexao, $dados) {
    $erros = array();

    // ---- Categoria: precisa existir em categorias_eventos ----
    if (!isset($dados['categoria_id']) || $dados['categoria_id'] === '' || !ctype_digit($dados['categoria_id'])) {
        $erros[] = 'Selecione uma categoria.';
    } else {
        $stmt = $conexao->prepare("SELECT id_categoria FROM categorias_eventos WHERE id_categoria = ? LIMIT 1");
        if ($stmt === false) {
            $erros[] = 'Não foi possível validar a categoria agora. Tente novamente.';
            registrar_log('Falha ao preparar validação de categoria: ' . $conexao->error, 'ERROR');
        } else {
            $categoriaId = (int) $dados['categoria_id'];
            $stmt->bind_param('i', $categoriaId);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 0) {
                $erros[] = 'A categoria selecionada não existe.';
            }
            $stmt->close();
        }
    }

    // ---- Título ----
    $titulo = isset($dados['titulo']) ? $dados['titulo'] : '';
    if ($titulo === '') {
        $erros[] = 'Informe o título do evento.';
    } elseif (strlen($titulo) > 200) {
        $erros[] = 'O título pode ter no máximo 200 caracteres.';
    }

    // ---- Descrição: obrigatória e com limite de tamanho ----
    $descricao = isset($dados['descricao']) ? trim($dados['descricao']) : '';
    if ($descricao === '') {
        $erros[] = 'Informe a descrição do evento.';
    } elseif (strlen($descricao) > 20000) {
        $erros[] = 'A descrição pode ter no máximo 20000 caracteres.';
    }

    // ---- Datas: formato real aaaa-mm-dd ----
    $dataInicio = isset($dados['data_inicio_evento']) ? $dados['data_inicio_evento'] : '';
    $dataFim = isset($dados['data_fim_evento']) ? $dados['data_fim_evento'] : '';

    if ($dataInicio === '') {
        $erros[] = 'Informe a data de início.';
    } elseif (!evento_data_valida($dataInicio)) {
        $erros[] = 'A data de início não é uma data válida.';
    }
    if ($dataFim === '') {
        $erros[] = 'Informe a data de término.';
    } elseif (!evento_data_valida($dataFim)) {
        $erros[] = 'A data de término não é uma data válida.';
    }
    if ($dataInicio !== '' && $dataFim !== ''
        && evento_data_valida($dataInicio) && evento_data_valida($dataFim)
        && $dataFim < $dataInicio) {
        $erros[] = 'A data de término não pode ser anterior à data de início.';
    }

    // ---- Horários: formato real hh:mm (ou hh:mm:ss) e fim > início ----
    $horaInicio = isset($dados['hora_inicio']) ? $dados['hora_inicio'] : '';
    $horaFim = isset($dados['hora_fim']) ? $dados['hora_fim'] : '';

    if ($horaInicio === '') {
        $erros[] = 'Informe o horário de início.';
    } elseif (!evento_hora_valida($horaInicio)) {
        $erros[] = 'O horário de início não é válido (use o formato 14:30).';
    }
    if ($horaFim === '') {
        $erros[] = 'Informe o horário de término.';
    } elseif (!evento_hora_valida($horaFim)) {
        $erros[] = 'O horário de término não é válido (use o formato 14:30).';
    }
    if ($horaInicio !== '' && $horaFim !== ''
        && evento_hora_valida($horaInicio) && evento_hora_valida($horaFim)
        && evento_hora_normalizar($horaFim) <= evento_hora_normalizar($horaInicio)) {
        $erros[] = 'O horário de término deve ser posterior ao horário de início.';
    }

    // ---- Local ----
    $endereco = isset($dados['endereco']) ? $dados['endereco'] : '';
    if ($endereco === '') {
        $erros[] = 'Informe o endereço.';
    } elseif (strlen($endereco) > 150) {
        $erros[] = 'O endereço pode ter no máximo 150 caracteres.';
    }
    if (isset($dados['numero']) && strlen($dados['numero']) > 10) {
        $erros[] = 'O número pode ter no máximo 10 caracteres.';
    }
    $cidade = isset($dados['cidade']) ? $dados['cidade'] : '';
    if ($cidade === '') {
        $erros[] = 'Informe a cidade.';
    } elseif (strlen($cidade) > 100) {
        $erros[] = 'A cidade pode ter no máximo 100 caracteres.';
    }
    $estado = isset($dados['estado']) ? $dados['estado'] : '';
    if ($estado === '' || !preg_match('/^[A-Za-z]{2}$/', $estado)) {
        $erros[] = 'Informe o estado (sigla com 2 letras, ex: SC).';
    }
    if (isset($dados['cep']) && $dados['cep'] !== '') {
        $cepDigitos = preg_replace('/\D/', '', $dados['cep']);
        if (strlen($cepDigitos) !== 8) {
            $erros[] = 'O CEP deve ter 8 números (ou deixe em branco).';
        }
    }

    // ---- Valor e vagas ----
    if (!isset($dados['valor']) || $dados['valor'] === '' || !is_numeric($dados['valor']) || (float) $dados['valor'] < 0) {
        $erros[] = 'Informe um valor válido (use 0 para evento gratuito).';
    } elseif ((float) $dados['valor'] > 99999999.99) {
        $erros[] = 'O valor informado é grande demais.';
    }
    if (!isset($dados['vagas']) || $dados['vagas'] === '' || !ctype_digit((string) $dados['vagas'])) {
        $erros[] = 'Informe a quantidade de vagas.';
    } elseif ((int) $dados['vagas'] < 1) {
        $erros[] = 'A quantidade de vagas deve ser pelo menos 1.';
    }

    return $erros;
}

/**
 * Confere se um valor é uma data real no formato aaaa-mm-dd.
 *
 * @param string $valor
 * @return bool
 */
function evento_data_valida($valor) {
    if (!is_string($valor) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $pedacos)) {
        return false;
    }
    return checkdate((int) $pedacos[2], (int) $pedacos[3], (int) $pedacos[1]);
}

/**
 * Confere se um valor é um horário real no formato hh:mm ou hh:mm:ss.
 *
 * @param string $valor
 * @return bool
 */
function evento_hora_valida($valor) {
    return is_string($valor)
        && preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $valor) === 1;
}

/**
 * Normaliza um horário para hh:mm:ss (comparações de ordenação).
 *
 * @param string $valor
 * @return string
 */
function evento_hora_normalizar($valor) {
    if (!is_string($valor)) {
        return '00:00:00';
    }
    $partes = explode(':', $valor);
    if (count($partes) === 2) {
        $partes[] = '00';
    }
    if (count($partes) !== 3) {
        return '00:00:00';
    }
    return str_pad($partes[0], 2, '0', STR_PAD_LEFT) . ':' . str_pad($partes[1], 2, '0', STR_PAD_LEFT) . ':' . str_pad($partes[2], 2, '0', STR_PAD_LEFT);
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
