<?php
/* ==========================================================
 * COMPONENTE: bootstrap.php
 * Ponto único de inicialização do EventoVivo. Cada tela inclui só este
 * arquivo e recebe: ambiente, erros em log, fuso horário, cabeçalhos de
 * segurança, sessão segura, conexão com o banco e helpers (escape de HTML,
 * CSRF, login e log).
 *
 * Uso (no topo de qualquer tela dentro de /Telas):
 *     require_once dirname(__FILE__) . '/../config/bootstrap.php';
 *
 * Compatível com PHP 5.3 ou superior (EasyPHP 5.3.9 incluso).
 * ========================================================== */

// Evita carregar duas vezes se alguma tela incluir o arquivo mais de uma vez.
if (defined('EVENTOVIVO_BOOTSTRAP')) {
    return;
}
define('EVENTOVIVO_BOOTSTRAP', true);

/* ----------------------------------------------------------
 * 0. Compatibilidade com PHP antigo (5.3)
 *    Recria funções e constantes que só existem em versões mais novas.
 *    Em PHP moderno nada disto é usado, pois as originais já existem.
 * ---------------------------------------------------------- */

// session_status() e constantes: nasceram no PHP 5.4.
if (!defined('PHP_SESSION_DISABLED')) {
    define('PHP_SESSION_DISABLED', 0);
    define('PHP_SESSION_NONE', 1);
    define('PHP_SESSION_ACTIVE', 2);
}
if (!function_exists('session_status')) {
    function session_status()
    {
        return session_id() === '' ? PHP_SESSION_NONE : PHP_SESSION_ACTIVE;
    }
}

// hash_equals(): nasceu no PHP 5.6. Comparação em tempo constante.
if (!function_exists('hash_equals')) {
    function hash_equals($conhecido, $informado)
    {
        $conhecido = (string) $conhecido;
        $informado = (string) $informado;
        if (strlen($conhecido) !== strlen($informado)) {
            return false;
        }
        $resultado = 0;
        for ($i = 0, $n = strlen($conhecido); $i < $n; $i++) {
            $resultado |= ord($conhecido[$i]) ^ ord($informado[$i]);
        }
        return $resultado === 0;
    }
}

// http_response_code(): nasceu no PHP 5.4.
if (!function_exists('http_response_code')) {
    function http_response_code($codigo = null)
    {
        if ($codigo !== null) {
            header('X-PHP-Response-Code: ' . (int) $codigo, true, (int) $codigo);
        }
        return $codigo;
    }
}

// ENT_SUBSTITUTE: nasceu no PHP 5.4.
if (!defined('ENT_SUBSTITUTE')) {
    define('ENT_SUBSTITUTE', 0);
}

/* ----------------------------------------------------------
 * 1. Caminhos e ambiente
 * ---------------------------------------------------------- */

// Raiz do projeto (a pasta que contém config/, Telas/, uploads/ ...).
define('BASE_PATH', dirname(dirname(__FILE__)));
define('LOG_PATH', BASE_PATH . '/logs');

// Tempo máximo sem atividade antes de encerrar a sessão (30 minutos).
define('SESSAO_TEMPO_MAXIMO', 30 * 60);

// Nome do campo oculto usado nos formulários para o token CSRF.
define('CSRF_CAMPO', 'csrf');

/**
 * Define o ambiente. Ordem de decisão:
 *  1. Variável de ambiente EVENTOVIVO_ENV ("development" ou "production").
 *  2. Se não existir: localhost / linha de comando = development.
 *  3. Qualquer outro servidor = production (mais seguro).
 */
function eventovivo_detectar_ambiente()
{
    $definido = getenv('EVENTOVIVO_ENV');
    if ($definido === 'development' || $definido === 'production') {
        return $definido;
    }

    if (php_sapi_name() === 'cli') {
        return 'development';
    }

    $host = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
    $locais = array('localhost', '127.0.0.1', '::1');
    if (in_array($host, $locais, true)) {
        return 'development';
    }

    return 'production';
}

define('APP_ENV', eventovivo_detectar_ambiente());
define('APP_DEBUG', APP_ENV === 'development');

/* ----------------------------------------------------------
 * 2. Erros e log
 *    Em produção o visitante nunca vê erro técnico; tudo vai para
 *    logs/php-errors.log.
 * ---------------------------------------------------------- */

if (!is_dir(LOG_PATH)) {
    @mkdir(LOG_PATH, 0755, true);
}
if (is_dir(LOG_PATH) && !file_exists(LOG_PATH . '/.htaccess')) {
    @file_put_contents(
        LOG_PATH . '/.htaccess',
        "# Bloqueia acesso web aos logs\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Order allow,deny\n    Deny from all\n</IfModule>\n"
    );
}

error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('display_startup_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', LOG_PATH . '/php-errors.log');

/**
 * Registra uma mensagem no log da aplicação (logs/app.log).
 * Use no lugar de mostrar detalhes técnicos para o usuário.
 *
 * @param string $mensagem Texto do evento ou erro.
 * @param string $nivel    INFO, WARNING ou ERROR.
 */
if (!function_exists('registrar_log')) {
    function registrar_log($mensagem, $nivel = 'ERROR')
    {
        $linha = '[' . date('Y-m-d H:i:s') . '] ' . $nivel . ' ' . $mensagem . "\n";
        @file_put_contents(LOG_PATH . '/app.log', $linha, FILE_APPEND | LOCK_EX);
    }
}

/* ----------------------------------------------------------
 * 3. Fuso horário e codificação
 * ---------------------------------------------------------- */

date_default_timezone_set('America/Sao_Paulo');
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

/* ----------------------------------------------------------
 * 4. Cabeçalhos de segurança
 * ---------------------------------------------------------- */

/**
 * Detecta se a requisição chegou por HTTPS (inclusive atrás de proxy).
 */
function eventovivo_https_ativo()
{
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
        return true;
    }
    return false;
}

if (php_sapi_name() !== 'cli' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if (eventovivo_https_ativo()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}

/* ----------------------------------------------------------
 * 5. Sessão segura
 *    As configurações só podem ser aplicadas antes da sessão iniciar.
 *    Por isso as telas NÃO devem chamar session_start() sozinhas.
 * ---------------------------------------------------------- */

if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.gc_maxlifetime', (string) SESSAO_TEMPO_MAXIMO);

    $seguro = eventovivo_https_ativo();

    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params(array(
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $seguro,
            'httponly' => true,
            'samesite' => 'Lax',
        ));
    } else {
        // Antes do PHP 7.3 o SameSite entra junto do path.
        session_set_cookie_params(0, '/; samesite=Lax', '', $seguro, true);
    }

    session_name('EVENTOVIVO_SESSID');
    session_start();

    // Expira a sessão após o tempo máximo sem atividade.
    if (isset($_SESSION['ultima_atividade'])
        && (time() - (int) $_SESSION['ultima_atividade']) > SESSAO_TEMPO_MAXIMO) {
        $estavaLogado = isset($_SESSION['id_usuario']);
        $_SESSION = array();
        session_destroy();
        session_start();
        session_regenerate_id(true);
        if ($estavaLogado) {
            $_SESSION['aviso_sessao'] = 'Sua sessão expirou por inatividade. Entre novamente.';
        }
    }
    $_SESSION['ultima_atividade'] = time();
}

/* ----------------------------------------------------------
 * 6. Conexão com o banco
 *    A variável $conexao continua disponível nas telas exatamente
 *    como antes, porque o conexao.php é carregado a partir daqui.
 * ---------------------------------------------------------- */

require_once BASE_PATH . '/config/conexao.php';

/* ----------------------------------------------------------
 * 7. Helpers
 * ---------------------------------------------------------- */

/**
 * Escapa texto para exibição segura em HTML (proteção contra XSS).
 * Exemplo: <?php echo e($evento['titulo']); ?>
 */
if (!function_exists('e')) {
    function e($valor)
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/**
 * Redireciona para outra página e encerra a execução.
 */
if (!function_exists('redirecionar')) {
    function redirecionar($destino)
    {
        header('Location: ' . $destino);
        exit;
    }
}

/**
 * Retorna (e cria, se preciso) o token CSRF da sessão.
 */
if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        if (empty($_SESSION['csrf_token'])) {
            if (function_exists('random_bytes')) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } elseif (function_exists('openssl_random_pseudo_bytes')) {
                $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
            } else {
                // Último recurso para PHP 5.3 sem a extensão OpenSSL.
                $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true)) . md5(uniqid(mt_rand(), true));
            }
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * Gera o campo oculto para colocar dentro de cada <form method="post">.
 * Exemplo: <?php echo csrf_campo(); ?>
 */
if (!function_exists('csrf_campo')) {
    function csrf_campo()
    {
        return '<input type="hidden" name="' . CSRF_CAMPO . '" value="' . e(csrf_token()) . '">';
    }
}

/**
 * Confere se o token enviado é igual ao da sessão.
 *
 * @param string|null $token Se omitido, lê de $_POST['csrf'].
 * @return bool
 */
if (!function_exists('csrf_validar')) {
    function csrf_validar($token = null)
    {
        if ($token === null) {
            $token = isset($_POST[CSRF_CAMPO]) ? $_POST[CSRF_CAMPO] : '';
        }
        if (empty($_SESSION['csrf_token']) || !is_string($token) || $token === '') {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

/**
 * Atalho para telas que recebem POST: se o token for inválido, responde
 * 403 e encerra. Chame logo no início do bloco de tratamento do POST.
 */
if (!function_exists('csrf_exigir')) {
    function csrf_exigir()
    {
        if (!csrf_validar()) {
            registrar_log('CSRF inválido em ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '?'), 'WARNING');
            http_response_code(403);
            exit('Requisição inválida. Volte à página anterior, atualize e tente novamente.');
        }
    }
}

/**
 * Indica se há um usuário autenticado na sessão.
 */
if (!function_exists('usuario_logado')) {
    function usuario_logado()
    {
        return !empty($_SESSION['id_usuario']);
    }
}

/**
 * Retorna o id do usuário logado (0 se ninguém estiver logado).
 */
if (!function_exists('usuario_id')) {
    function usuario_id()
    {
        return isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : 0;
    }
}

/**
 * Exige login. Quem não estiver autenticado vai para a tela de Login.
 */
if (!function_exists('exigir_login')) {
    function exigir_login($destino = 'Login.php')
    {
        if (!usuario_logado()) {
            redirecionar($destino);
        }
    }
}

/**
 * Atalho para exibir e consumir o aviso de sessão expirada (uma vez só).
 *
 * @return string Mensagem, ou string vazia se não houver aviso.
 */
if (!function_exists('aviso_sessao')) {
    function aviso_sessao()
    {
        if (empty($_SESSION['aviso_sessao'])) {
            return '';
        }
        $mensagem = $_SESSION['aviso_sessao'];
        unset($_SESSION['aviso_sessao']);
        return $mensagem;
    }
}
