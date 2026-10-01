<?php
/* ==========================================================
 * COMPONENTE: funcoes_admin.php
 * Biblioteca do painel administrativo: controle de acesso do ADM,
 * registro de atividade do site e consultas agregadas usadas
 * para montar os indicadores e gráficos do painel.
 * ========================================================== */

/**
 * funcoes_admin.php
 *
 * Todas as funções recebem $conexao (objeto mysqli) já aberto por
 * config/conexao.php.
 */

// Janela (em minutos) considerada como "usando o site agora".
define('ADMIN_ONLINE_MINUTOS', 5);

// Quantos dias de histórico ficam guardados na tabela de acessos.
define('ADMIN_HISTORICO_DIAS', 90);

/**
 * Descobre o tipo (usuario / empresa / admin) de quem está logado.
 *
 * Usa o tipo guardado na sessão; quando a sessão é antiga e não tem
 * o tipo (logins anteriores a esta versão), consulta o banco uma vez
 * e devolve o valor para a sessão.
 *
 * @param mysqli $conexao
 * @return string 'usuario', 'empresa', 'admin' ou '' se não logado
 */
function admin_tipo_usuario($conexao) {
    if (!isset($_SESSION['id_usuario'])) {
        return '';
    }

    if (isset($_SESSION['tipo_usuario'])) {
        return $_SESSION['tipo_usuario'];
    }

    $stmt = $conexao->prepare("SELECT tipo FROM usuario WHERE id_usuario = ? LIMIT 1");
    $stmt->bind_param('i', $_SESSION['id_usuario']);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $linha = $resultado->fetch_assoc();
    $stmt->close();

    $tipo = $linha ? $linha['tipo'] : 'usuario';
    $_SESSION['tipo_usuario'] = $tipo;

    return $tipo;
}

/**
 * Bloqueia o acesso de quem não for administrador.
 * Sem sessão vai para o Login; logado sem perfil ADM volta para
 * o painel de eventos (usuário comum).
 */
function admin_exigir_permissao($conexao) {
    if (!isset($_SESSION['id_usuario'])) {
        header('Location: Login.php');
        exit;
    }

    if (admin_tipo_usuario($conexao) !== 'admin') {
        header('Location: CRUD_Eventos.php');
        exit;
    }
}

/**
 * Garante que a tabela de acessos exista.
 *
 * O painel funciona mesmo que o arquivo .sql da pasta "banco de dados"
 * não tenha sido importado: a tabela é criada na primeira visita.
 *
 * @param mysqli $conexao
 */
function admin_garantir_tabela_acessos($conexao) {
    $sql = "CREATE TABLE IF NOT EXISTS acesso_sistema (
                id_acesso INT(11) NOT NULL AUTO_INCREMENT,
                usuario_id INT(11) DEFAULT NULL,
                sessao VARCHAR(90) DEFAULT NULL,
                pagina VARCHAR(200) DEFAULT NULL,
                endereco_ip VARCHAR(45) DEFAULT NULL,
                data_acesso DATETIME NOT NULL,
                PRIMARY KEY (id_acesso),
                KEY idx_acesso_data (data_acesso),
                KEY idx_acesso_usuario (usuario_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $conexao->query($sql);
}

/**
 * Registra o acesso da requisição atual (base do indicador
 * "pessoas usando o site").
 *
 * @param mysqli  $conexao
 * @param int|null $usuarioId id do usuário logado (ou null)
 * @param string  $pagina    página acessada
 */
function admin_registrar_acesso($conexao, $usuarioId, $pagina) {
    admin_garantir_tabela_acessos($conexao);

    $sessao = session_id();
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $pagina = substr($pagina, 0, 200);

    $stmt = $conexao->prepare(
        "INSERT INTO acesso_sistema (usuario_id, sessao, pagina, endereco_ip, data_acesso)
         VALUES (?, ?, ?, ?, NOW())"
    );
    $stmt->bind_param('isss', $usuarioId, $sessao, $pagina, $ip);
    $stmt->execute();
    $stmt->close();

    // Apaga o histórico antigo de vez em quando (1 em cada 25 visitas),
    // para o banco não crescer sem limite.
    if (mt_rand(1, 25) === 1) {
        $conexao->query(
            "DELETE FROM acesso_sistema
             WHERE data_acesso < DATE_SUB(NOW(), INTERVAL " . (int) ADMIN_HISTORICO_DIAS . " DAY)"
        );
    }
}

/**
 * Lê um inteiro não-negativo de $_GET.
 *
 * ctype_digit() no PHP 5.3 interpreta inteiros como código ASCII, então
 * o valor é convertido para string antes da checagem. Qualquer coisa que
 * não seja só dígitos volta como 0 (ou $padrao).
 *
 * @param string $chave
 * @param int    $padrao
 * @return int
 */
function admin_int_de_get($chave, $padrao = 0) {
    if (!isset($_GET[$chave]) || is_array($_GET[$chave])) {
        return $padrao;
    }

    $valor = trim((string) $_GET[$chave]);

    if ($valor === '' || !ctype_digit($valor)) {
        return $padrao;
    }

    return (int) $valor;
}

/**
 * Normaliza os filtros recebidos por GET.
 *
 * @return array array('periodo' => int, 'uf' => string, 'cidade' => string, 'tipo' => string)
 */
function admin_ler_filtros() {
    $periodos = array(7, 30, 90, 0);

    $periodo = admin_int_de_get('periodo', 30);

    if (!in_array($periodo, $periodos)) {
        $periodo = 30;
    }

    $uf = isset($_GET['uf']) && !is_array($_GET['uf']) ? strtoupper(trim($_GET['uf'])) : '';
    if (strlen($uf) !== 2) {
        $uf = '';
    }

    $cidade = isset($_GET['cidade']) && !is_array($_GET['cidade']) ? trim($_GET['cidade']) : '';

    $tipos = array('todos', 'usuario', 'empresa', 'admin');
    $tipo = isset($_GET['tipo']) && in_array($_GET['tipo'], $tipos) ? $_GET['tipo'] : 'todos';

    return array(
        'periodo' => $periodo,
        'uf' => $uf,
        'cidade' => $cidade,
        'tipo' => $tipo,
    );
}

/**
 * Monta a string de datas ("a.data >= DATE_SUB(NOW(), INTERVAL N DAY)")
 * conforme o filtro de período. Devolve string vazia quando o período
 * é "todos".
 *
 * @param int $periodo
 * @return string
 */
function admin_clausula_periodo($periodo) {
    if ($periodo <= 0) {
        return '';
    }

    return " AND data_acesso >= DATE_SUB(NOW(), INTERVAL " . (int) $periodo . " DAY)";
}

/**
 * Monta (com placeholders) a cláusula de filtro de cidade/UF/tipo
 * sobre a tabela usuario. Devolve array com [sql, tipos, parametros].
 *
 * @param array $filtros
 * @param string $alias   alias da tabela usuario na consulta
 * @return array
 */
function admin_clausula_usuario($filtros, $alias) {
    $sql = '';
    $tipos = '';
    $parametros = array();

    if ($filtros['uf'] !== '') {
        $sql .= " AND {$alias}.estado = ?";
        $tipos .= 's';
        $parametros[] = $filtros['uf'];
    }

    if ($filtros['cidade'] !== '') {
        $sql .= " AND {$alias}.cidade LIKE ?";
        $tipos .= 's';
        $parametros[] = '%' . $filtros['cidade'] . '%';
    }

    if ($filtros['tipo'] !== 'todos') {
        $sql .= " AND {$alias}.tipo = ?";
        $tipos .= 's';
        $parametros[] = $filtros['tipo'];
    }

    return array($sql, $tipos, $parametros);
}

/**
 * Executa uma query preparada com quantidade variável de parâmetros.
 * Monta os argumentos por referência, como o bind_param exige.
 *
 * @param mysqli $conexao
 * @param string $sql
 * @param string $tipos
 * @param array  $parametros
 * @return array lista de linhas associativas
 */
function admin_consultar($conexao, $sql, $tipos = '', $parametros = array()) {
    $linhas = array();

    $stmt = $conexao->prepare($sql);
    if ($stmt === false) {
        return $linhas;
    }

    if ($tipos !== '') {
        $argumentos = array($tipos);
        for ($i = 0; $i < count($parametros); $i++) {
            $argumentos[] = &$parametros[$i];
        }
        call_user_func_array(array($stmt, 'bind_param'), $argumentos);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();

    while ($linha = $resultado->fetch_assoc()) {
        $linhas[] = $linha;
    }

    $stmt->close();

    return $linhas;
}

/**
 * INDICADOR 1 — Quantas pessoas estão usando o site.
 *
 * @param mysqli $conexao
 * @param array  $filtros
 * @return array
 */
function admin_indicador_acesso($conexao, $filtros) {
    admin_garantir_tabela_acessos($conexao);

    $vazio = array(
        'online' => 0,
        'online_logados' => 0,
        'ativos' => 0,
        'acessos' => 0,
        'media_por_usuario' => 0,
    );

    $lista = admin_consultar(
        $conexao,
        "SELECT COUNT(*) AS total,
                COUNT(DISTINCT usuario_id) AS usuarios,
                SUM(usuario_id IS NULL) AS anonimos
         FROM acesso_sistema a
         WHERE usuario_id IS NULL
            OR usuario_id IN (SELECT id_usuario FROM usuario)" .
        admin_clausula_periodo($filtros['periodo'])
    );

    if (empty($lista)) {
        return $vazio;
    }

    $acesso = $lista[0];

    $listaOnline = admin_consultar(
        $conexao,
        "SELECT COUNT(DISTINCT sessao) AS sessoes,
                COUNT(DISTINCT usuario_id) AS usuarios
         FROM acesso_sistema a
         WHERE data_acesso >= DATE_SUB(NOW(), INTERVAL " . (int) ADMIN_ONLINE_MINUTOS . " MINUTE)
           AND usuario_id IS NOT NULL"
    );

    $sessoes = isset($listaOnline[0]['sessoes']) ? (int) $listaOnline[0]['sessoes'] : 0;
    $usuariosOnline = isset($listaOnline[0]['usuarios']) ? (int) $listaOnline[0]['usuarios'] : 0;

    $acessos = (int) $acesso['total'];
    $ativos = (int) $acesso['usuarios'];

    return array(
        'online' => $sessoes,
        'online_logados' => $usuariosOnline,
        'ativos' => $ativos,
        'acessos' => $acessos,
        'media_por_usuario' => $ativos > 0 ? round($acessos / $ativos, 1) : 0,
    );
}

/**
 * Série de acessos por dia, para o gráfico de barras.
 *
 * @param mysqli $conexao
 * @param array  $filtros
 * @param int    $dias
 * @return array lista de array('dia' => 'aaaa-mm-dd', 'total' => int)
 */
function admin_acessos_por_dia($conexao, $filtros, $dias) {
    admin_garantir_tabela_acessos($conexao);

    $dias = (int) $dias;
    if ($dias < 1) {
        $dias = 14;
    }

    // Chave do período, reaproveitando o filtro global quando ele é menor.
    if ($filtros['periodo'] > 0 && $filtros['periodo'] < $dias) {
        $dias = $filtros['periodo'];
    }

    $linhas = admin_consultar(
        $conexao,
        "SELECT DATE(a.data_acesso) AS dia, COUNT(*) AS total
         FROM acesso_sistema a
         WHERE a.data_acesso >= DATE_SUB(CURDATE(), INTERVAL " . ($dias - 1) . " DAY)" .
        admin_clausula_periodo($filtros['periodo']) .
        " GROUP BY DATE(a.data_acesso)"
    );

    $porDia = array();
    foreach ($linhas as $linha) {
        $porDia[$linha['dia']] = (int) $linha['total'];
    }

    // Preenche os dias sem movimento para o gráfico não ficar com buracos.
    $serie = array();
    for ($i = $dias - 1; $i >= 0; $i--) {
        $dia = date('Y-m-d', strtotime('-' . $i . ' day'));
        $serie[] = array(
            'dia' => $dia,
            'total' => isset($porDia[$dia]) ? $porDia[$dia] : 0,
        );
    }

    return $serie;
}

/**
 * Base de usuários: total, por tipo, artistas e empresas, já
 * respeitando os filtros de cidade/UF/tipo.
 *
 * @param mysqli $conexao
 * @param array  $filtros
 * @return array
 */
function admin_indicador_usuarios($conexao, $filtros) {
    $clausula = admin_clausula_usuario($filtros, 'u');
    $lista = $clausula;
    $tipos = $clausula[1];
    $parametros = $clausula[2];

    $linhas = admin_consultar(
        $conexao,
        "SELECT
            COUNT(*) AS total,
            SUM(u.tipo = 'usuario') AS usuarios,
            SUM(u.tipo = 'empresa') AS empresas,
            SUM(u.tipo = 'admin') AS admins
         FROM usuario u
         WHERE 1 = 1" . $lista[0],
        $tipos,
        $parametros
    );

    $linha = empty($linhas) ? null : $linhas[0];

    $porTipo = admin_consultar(
        $conexao,
        "SELECT u.tipo AS tipo, COUNT(*) AS total
         FROM usuario u
         WHERE 1 = 1" . $lista[0] .
        " GROUP BY u.tipo
          ORDER BY total DESC",
        $tipos,
        $parametros
    );

    $porCidade = admin_consultar(
        $conexao,
        "SELECT u.cidade AS cidade, u.estado AS estado, COUNT(*) AS total
         FROM usuario u
         WHERE 1 = 1" . $lista[0] .
        " GROUP BY u.estado, u.cidade
          ORDER BY total DESC, u.cidade ASC",
        $tipos,
        $parametros
    );

    return array(
        'total' => $linha ? (int) $linha['total'] : 0,
        'usuarios' => $linha ? (int) $linha['usuarios'] : 0,
        'empresas' => $linha ? (int) $linha['empresas'] : 0,
        'admins' => $linha ? (int) $linha['admins'] : 0,
        'por_tipo' => $porTipo,
        'por_cidade' => $porCidade,
    );
}

/**
 * INDICADOR 2 — Quantos artistas (freelancers) existem em cada
 * categoria de serviço.
 *
 * @param mysqli $conexao
 * @param array  $filtros
 * @return array lista de array('id_categoria', 'nome', 'descricao', 'total', 'avaliacoes', 'media')
 */
function admin_artistas_por_categoria($conexao, $filtros) {
    $clausula = admin_clausula_usuario($filtros, 'u');
    $lista = $clausula[0];
    $tipos = $clausula[1];
    $parametros = $clausula[2];

    $linhas = admin_consultar(
        $conexao,
        "SELECT cs.id_categoria,
                cs.nome,
                cs.descricao,
                COUNT(f.id_freelancer) AS total,
                COUNT(a.id) AS avaliacoes,
                AVG(a.nota) AS media
         FROM categorias_servicos cs
         LEFT JOIN freelancers f ON f.categoria_id = cs.id_categoria
         LEFT JOIN usuario u ON u.id_usuario = f.usuario_id
         LEFT JOIN avaliacoes a ON a.freelancer_id = f.id_freelancer
         WHERE 1 = 1" . $lista . "
         GROUP BY cs.id_categoria, cs.nome, cs.descricao
         ORDER BY total DESC, cs.nome ASC",
        $tipos,
        $parametros
    );

    foreach ($linhas as $i => $linha) {
        $linhas[$i]['total'] = (int) $linha['total'];
        $linhas[$i]['avaliacoes'] = (int) $linha['avaliacoes'];
        $linhas[$i]['media'] = $linha['media'] === null ? 0 : round((float) $linha['media'], 1);
    }

    return $linhas;
}

/**
 * INDICADOR 3 — Eventos cadastrados: total, próximos, encerrados,
 * receita potencial e distribuição por categoria.
 *
 * @param mysqli $conexao
 * @param array  $filtros
 * @return array
 */
function admin_indicador_eventos($conexao, $filtros) {
    $clausula = admin_clausula_usuario($filtros, 'u');
    $lista = $clausula[0];
    $tipos = $clausula[1];
    $parametros = $clausula[2];

    $linhas = admin_consultar(
        $conexao,
        "SELECT
            COUNT(e.id_evento) AS total,
            SUM(e.data_fim_evento >= CURDATE()) AS proximos,
            SUM(e.data_fim_evento < CURDATE()) AS encerrados,
            SUM(e.valor > 0) AS pagos,
            COALESCE(SUM(e.valor), 0) AS receita,
            COALESCE(SUM(e.vagas), 0) AS vagas
         FROM eventos e
         LEFT JOIN usuario u ON u.id_usuario = e.usuario_id
         WHERE 1 = 1" . $lista,
        $tipos,
        $parametros
    );

    $linha = empty($linhas) ? null : $linhas[0];

    $porCategoria = admin_consultar(
        $conexao,
        "SELECT ce.id_categoria,
                ce.nome,
                COUNT(e.id_evento) AS total,
                SUM(e.data_fim_evento >= CURDATE()) AS proximos
         FROM categorias_eventos ce
         LEFT JOIN eventos e ON e.categoria_id = ce.id_categoria
         LEFT JOIN usuario u ON u.id_usuario = e.usuario_id
         WHERE 1 = 1" . $lista . "
         GROUP BY ce.id_categoria, ce.nome
         ORDER BY total DESC, ce.nome ASC",
        $tipos,
        $parametros
    );

    foreach ($porCategoria as $i => $categoria) {
        $porCategoria[$i]['total'] = (int) $categoria['total'];
        $porCategoria[$i]['proximos'] = (int) $categoria['proximos'];
    }

    return array(
        'total' => $linha ? (int) $linha['total'] : 0,
        'proximos' => $linha ? (int) $linha['proximos'] : 0,
        'encerrados' => $linha ? (int) $linha['encerrados'] : 0,
        'pagos' => $linha ? (int) $linha['pagos'] : 0,
        'receita' => $linha ? (float) $linha['receita'] : 0,
        'vagas' => $linha ? (int) $linha['vagas'] : 0,
        'por_categoria' => $porCategoria,
    );
}

/**
 * INDICADOR 4 — Ranking de artistas.
 *
 * Critérios possíveis: avaliacao (nota média), avaliacoes (quantidade),
 * favoritos, cadastros (mais recentes).
 *
 * @param mysqli $conexao
 * @param array  $filtros
 * @param int    $categoriaFiltro 0 = todas
 * @param string $criterio
 * @param int    $limite
 * @return array
 */
function admin_ranking_artistas($conexao, $filtros, $categoriaFiltro, $criterio, $limite) {
    $clausula = admin_clausula_usuario($filtros, 'u');
    $lista = $clausula[0];
    $tipos = $clausula[1];
    $parametros = $clausula[2];

    if ($categoriaFiltro > 0) {
        $lista .= " AND f.categoria_id = ?";
        $tipos .= 'i';
        $parametros[] = $categoriaFiltro;
    }

    $sql = "SELECT
                f.id_freelancer,
                u.nome AS nome,
                u.cidade AS cidade,
                u.estado AS estado,
                f.profissao AS profissao,
                f.valor_hora AS valor_hora,
                cs.nome AS categoria,
                COUNT(DISTINCT a.id) AS avaliacoes,
                AVG(a.nota) AS media,
                (SELECT COUNT(*) FROM favoritos fa WHERE fa.freelancer_id = f.id_freelancer) AS favoritos
            FROM freelancers f
            JOIN usuario u ON u.id_usuario = f.usuario_id
            LEFT JOIN categorias_servicos cs ON cs.id_categoria = f.categoria_id
            LEFT JOIN avaliacoes a ON a.freelancer_id = f.id_freelancer
            WHERE 1 = 1" . $lista . "
            GROUP BY f.id_freelancer, u.nome, u.cidade, u.estado,
                     f.profissao, f.valor_hora, cs.nome";

    $ordenacoes = array(
        'avaliacao' => "media DESC, avaliacoes DESC, favoritos DESC, u.nome ASC",
        'avaliacoes' => "avaliacoes DESC, media DESC, favoritos DESC, u.nome ASC",
        'favoritos' => "favoritos DESC, media DESC, avaliacoes DESC, u.nome ASC",
        'cadastros' => "f.id_freelancer DESC",
    );

    $sql .= " ORDER BY " . $ordenacoes[$criterio] . " LIMIT " . (int) $limite;

    $linhas = admin_consultar($conexao, $sql, $tipos, $parametros);

    foreach ($linhas as $i => $linha) {
        $linhas[$i]['media'] = $linha['media'] === null ? null : round((float) $linha['media'], 1);
        $linhas[$i]['avaliacoes'] = (int) $linha['avaliacoes'];
        $linhas[$i]['favoritos'] = (int) $linha['favoritos'];
        $linhas[$i]['valor_hora'] = $linha['valor_hora'] === null ? 0 : (float) $linha['valor_hora'];
        $linhas[$i]['posicao'] = $i + 1;
    }

    return $linhas;
}

/**
 * Lista de eventos com busca e filtros próprios da tabela do painel.
 *
 * @param mysqli $conexao
 * @param array  $filtros
 * @param string $busca
 * @param int    $categoriaFiltro
 * @param string $situacao   todos | proximos | encerrados
 * @param string $ordem      recentes | inicio | titulo | vagas
 * @param int    $limite
 * @return array
 */
function admin_listar_eventos($conexao, $filtros, $busca, $categoriaFiltro, $situacao, $ordem, $limite) {
    $clausula = admin_clausula_usuario($filtros, 'u');
    $lista = $clausula[0];
    $tipos = $clausula[1];
    $parametros = $clausula[2];

    if ($busca !== '') {
        $lista .= " AND (e.titulo LIKE ? OR e.cidade LIKE ? OR u.nome LIKE ?)";
        $tipos .= 'sss';
        $termo = '%' . $busca . '%';
        $parametros[] = $termo;
        $parametros[] = $termo;
        $parametros[] = $termo;
    }

    if ($categoriaFiltro > 0) {
        $lista .= " AND e.categoria_id = ?";
        $tipos .= 'i';
        $parametros[] = $categoriaFiltro;
    }

    if ($situacao === 'proximos') {
        $lista .= " AND e.data_fim_evento >= CURDATE()";
    } elseif ($situacao === 'encerrados') {
        $lista .= " AND e.data_fim_evento < CURDATE()";
    }

    $ordenacoes = array(
        'recentes' => "e.data_publicacao DESC, e.id_evento DESC",
        'inicio' => "e.data_inicio_evento ASC",
        'titulo' => "e.titulo ASC",
        'vagas' => "e.vagas DESC",
    );

    $sql = "SELECT e.id_evento,
                   e.titulo,
                   e.cidade,
                   e.estado,
                   e.data_inicio_evento,
                   e.data_fim_evento,
                   e.valor,
                   e.vagas,
                   e.data_publicacao,
                   ce.nome AS categoria,
                   u.nome AS publicado_por,
                   (SELECT COUNT(*) FROM favoritos fe WHERE fe.evento_id = e.id_evento) AS favoritos
            FROM eventos e
            LEFT JOIN categorias_eventos ce ON ce.id_categoria = e.categoria_id
            LEFT JOIN usuario u ON u.id_usuario = e.usuario_id
            WHERE 1 = 1" . $lista . "
            ORDER BY " . $ordenacoes[$ordem] . "
            LIMIT " . (int) $limite;

    $linhas = admin_consultar($conexao, $sql, $tipos, $parametros);

    foreach ($linhas as $i => $linha) {
        $linhas[$i]['id_evento'] = (int) $linha['id_evento'];
        $linhas[$i]['vagas'] = (int) $linha['vagas'];
        $linhas[$i]['favoritos'] = (int) $linha['favoritos'];
        $linhas[$i]['valor'] = (float) $linha['valor'];
        $linhas[$i]['ativo'] = $linha['data_fim_evento'] >= date('Y-m-d');
    }

    return $linhas;
}

/**
 * Contadores rápidos da barra lateral do painel.
 *
 * @param mysqli $conexao
 * @return array
 */
function admin_indicadores_sociais($conexao) {
    $linhas = admin_consultar(
        $conexao,
        "SELECT
            (SELECT COUNT(*) FROM freelancers) AS artistas,
            (SELECT COUNT(*) FROM mensagens) AS mensagens,
            (SELECT COUNT(*) FROM avaliacoes) AS avaliacoes,
            (SELECT COUNT(*) FROM favoritos) AS favoritos,
            (SELECT COUNT(*) FROM portfolio) AS portfolios,
            (SELECT COUNT(*) FROM notificacoes WHERE lida = 0) AS notificacoes_abertas"
    );

    $linha = empty($linhas) ? null : $linhas[0];

    if (!$linha) {
        return array(
            'artistas' => 0, 'mensagens' => 0, 'avaliacoes' => 0,
            'favoritos' => 0, 'portfolios' => 0, 'notificacoes_abertas' => 0,
        );
    }

    return array(
        'artistas' => (int) $linha['artistas'],
        'mensagens' => (int) $linha['mensagens'],
        'avaliacoes' => (int) $linha['avaliacoes'],
        'favoritos' => (int) $linha['favoritos'],
        'portfolios' => (int) $linha['portfolios'],
        'notificacoes_abertas' => (int) $linha['notificacoes_abertas'],
    );
}

/**
 * Monta a querystring preservando os filtros atuais.
 *
 * @param array $filtros
 * @param array $extras
 * @return string
 */
function admin_link_com_filtros($filtros, $extras = array()) {
    $dados = array(
        'periodo' => $filtros['periodo'],
        'uf' => $filtros['uf'],
        'cidade' => $filtros['cidade'],
        'tipo' => $filtros['tipo'],
    );

    foreach ($extras as $chave => $valor) {
        $dados[$chave] = $valor;
    }

    return 'PainelAdmin.php?' . http_build_query($dados);
}

/**
 * Formata a data curta (dd/mm) usada nos rótulos do gráfico.
 *
 * @param string $data
 * @return string
 */
function admin_dia_curto($data) {
    return date('d/m', strtotime($data));
}

/**
 * Lista de UFs que já têm usuários cadastrados, para montar o
 * <select> do filtro de estado.
 *
 * @param mysqli $conexao
 * @return array
 */
function admin_lista_ufs($conexao) {
    $ufs = array();

    $resultado = $conexao->query(
        "SELECT DISTINCT estado FROM usuario
         WHERE estado IS NOT NULL AND estado <> ''
         ORDER BY estado ASC"
    );

    if ($resultado) {
        while ($linha = $resultado->fetch_assoc()) {
            $ufs[] = $linha['estado'];
        }
        $resultado->free();
    }

    return $ufs;
}

/**
 * Estrelas compactas para as tabelas do painel.
 *
 * @param float|null $media
 * @return string HTML
 */
function admin_render_estrelas($media) {
    if ($media === null) {
        return '<span class="adm-sem-nota">Sem avaliações</span>';
    }

    $media = (float) $media;
    $html = '<span class="adm-estrelas">';

    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($media)) {
            $html .= '★';
        } elseif ($i - 0.5 <= $media) {
            $html .= '★';
        } else {
            $html .= '<span class="vazia">★</span>';
        }
    }

    $html .= '</span> <span class="adm-sem-nota">' . number_format($media, 1, ',', '.') . '</span>';

    return $html;
}

/**
 * Escala em porcentagem para as barras do gráfico.
 *
 * @param int $valor
 * @param int $maximo
 * @return int 0 a 100
 */
function admin_porcentagem($valor, $maximo) {
    if ($maximo <= 0 || $valor <= 0) {
        return 0;
    }

    return (int) round(($valor / $maximo) * 100);
}

