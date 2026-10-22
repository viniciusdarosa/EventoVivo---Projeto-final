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

// password_hash()/password_verify()/password_needs_rehash(): nasceram no
// PHP 5.5. No PHP 5.3.9 (EasyPHP) usamos um equivalente baseado em crypt()
// com Blowfish ($2y$), que já está disponível neste PHP (verificado).
if (!defined('PASSWORD_BCRYPT')) {
    define('PASSWORD_BCRYPT', 1);
    define('PASSWORD_DEFAULT', PASSWORD_BCRYPT);
}
if (!defined('PASSWORD_BCRYPT_DEFAULT_COST')) {
    define('PASSWORD_BCRYPT_DEFAULT_COST', 10);
}

/**
 * Gera $tamanho bytes aleatórios criptograficamente seguros, com fallbacks
 * para PHP antigo (OpenSSL) e, em último caso, fonte fraca (melhor esforço).
 *
 * @param int $tamanho
 * @return string bytes (length === $tamanho)
 */
if (!function_exists('ev_random_bytes')) {
    function ev_random_bytes($tamanho)
    {
        $tamanho = (int) $tamanho;
        if ($tamanho < 1) {
            $tamanho = 1;
        }

        if (function_exists('random_bytes')) {
            try {
                return random_bytes($tamanho);
            } catch (Exception $e) {
                // continua nos próximos fallbacks
            }
        }

        if (function_exists('openssl_random_pseudo_bytes')) {
            $forte = false;
            $bytes = openssl_random_pseudo_bytes($tamanho, $forte);
            if ($bytes !== false && $forte) {
                return $bytes;
            }
        }

        // Último recurso: combinação de fontes fracas (PHP 5.3 sem OpenSSL).
        $saida = '';
        for ($i = 0; $i < $tamanho; $i++) {
            $saida .= chr(mt_rand(0, 255));
        }
        return substr(hash('sha256', $saida . uniqid('', true) . microtime(true), true), 0, $tamanho);
    }
}

if (!function_exists('password_hash')) {
    /**
     * Equivalente de password_hash() (bcrypt) para PHP 5.3 via crypt().
     *
     * @param string $senha
     * @param int    $algoritmo Somente PASSWORD_BCRYPT é suportado.
     * @param array  $opcoes    Aceita 'cost' (4..31).
     * @return string|false
     */
    function password_hash($senha, $algoritmo = PASSWORD_DEFAULT, $opcoes = array())
    {
        if (!is_string($senha) || $algoritmo !== PASSWORD_BCRYPT) {
            return false;
        }
        $cost = isset($opcoes['cost']) ? (int) $opcoes['cost'] : PASSWORD_BCRYPT_DEFAULT_COST;
        if ($cost < 4 || $cost > 31) {
            $cost = PASSWORD_BCRYPT_DEFAULT_COST;
        }

        // Salt bcrypt: 22 caracteres da lista ./A-Za-z0-9 (22 bytes codificados
        // em base64 com '+' trocado por '.' e padding removido).
        $salt = strtr(rtrim(base64_encode(ev_random_bytes(16)), '='), '+', '.');

        $hash = crypt($senha, '$2y$' . str_pad($cost, 2, '0', STR_PAD_LEFT) . '$' . $salt);
        if (!is_string($hash) || strlen($hash) !== 60 || substr($hash, 0, 4) !== '$2y$') {
            return false;
        }
        return $hash;
    }
}

if (!function_exists('password_verify')) {
    /**
     * Equivalente de password_verify() para PHP 5.3 via crypt().
     * Comparação em tempo constante com hash_equals().
     *
     * @param string $senha
     * @param string $hash
     * @return bool
     */
    function password_verify($senha, $hash)
    {
        if (!is_string($senha) || !is_string($hash) || strlen($hash) !== 60 || $hash[0] !== '$') {
            return false;
        }
        $calculado = crypt($senha, $hash);
        if (!is_string($calculado) || strlen($calculado) !== 60) {
            return false;
        }
        return hash_equals($hash, $calculado);
    }
}

if (!function_exists('password_needs_rehash')) {
    /**
     * Equivalente de password_needs_rehash() para PHP 5.3.
     *
     * @param string $hash
     * @param int    $algoritmo
     * @param array  $opcoes
     * @return bool
     */
    function password_needs_rehash($hash, $algoritmo = PASSWORD_DEFAULT, $opcoes = array())
    {
        if (!is_string($hash) || $algoritmo !== PASSWORD_BCRYPT || strlen($hash) !== 60) {
            return true;
        }
        $cost = isset($opcoes['cost']) ? (int) $opcoes['cost'] : PASSWORD_BCRYPT_DEFAULT_COST;
        if ($cost < 4 || $cost > 31) {
            $cost = PASSWORD_BCRYPT_DEFAULT_COST;
        }
        $prefixo = '$2y$' . str_pad($cost, 2, '0', STR_PAD_LEFT) . '$';
        return substr($hash, 0, 7) !== $prefixo;
    }
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

/**
 * Lê um valor textual de $_GET/$_POST com segurança.
 * Rejeita arrays (ex.: ?nome[]=x) e aplica trim, evitando warnings do PHP
 * ao passar array para trim() e evitando arrays dentro dos prepared statements.
 *
 * @param array  $origem  Normalmente $_GET ou $_POST.
 * @param string $chave   Nome do campo.
 * @param string $padrao  Valor de retorno quando ausente/inválido.
 * @return string
 */
if (!function_exists('entrada_texto')) {
    function entrada_texto($origem, $chave, $padrao = '')
    {
        if (!is_array($origem) || !isset($origem[$chave]) || !is_string($origem[$chave])) {
            return $padrao;
        }
        return trim($origem[$chave]);
    }
}

/**
 * Lê um valor inteiro de $_GET/$_POST com segurança.
 * Só aceita dígitos (com sinal opcional); arrays e lixo retornam $padrao.
 *
 * @param array  $origem Normalmente $_GET ou $_POST.
 * @param string $chave  Nome do campo.
 * @param int    $padrao Valor de retorno quando ausente/inválido.
 * @return int
 */
if (!function_exists('entrada_inteiro')) {
    function entrada_inteiro($origem, $chave, $padrao = 0)
    {
        $valor = entrada_texto($origem, $chave, '');
        if ($valor === '' || !preg_match('/^-?[0-9]+$/', $valor)) {
            return (int) $padrao;
        }
        return (int) $valor;
    }
}

/**
 * Aceita somente destinos internos de redirecionamento (proteção contra
 * open redirect). Formato permitido: página .php opcionalmente com
 * querystring simples de segurança (ex.: PerfilFreelancer.php?id=12).
 * Qualquer outra coisa (http:, //, \\, \r\n, javascript:, caminhos com
 * ../ etc.) cai no padrão.
 *
 * @param string $destino Valor vindo do usuário.
 * @param string $padrao  Destino seguro de retorno.
 * @return string Destino validado.
 */
if (!function_exists('destino_interno_seguro')) {
    function destino_interno_seguro($destino, $padrao = 'Home.php')
    {
        if (!is_string($destino) || $destino === '') {
            return $padrao;
        }
        if (preg_match('#^[A-Za-z0-9_-]+\.php(\?[A-Za-z0-9_=&%.-]+)?$#', $destino)) {
            return $destino;
        }
        return $padrao;
    }
}

/* ----------------------------------------------------------
 * 8. Limite de tentativas de login (proteção contra força bruta)
 *    Conta falhas por e-mail + IP em arquivo em logs/ (protegido
 *    por .htaccess). Após LOGIN_TENTATIVAS_MAX falhas dentro da
 *    janela, o login é bloqueado temporariamente.
 * ---------------------------------------------------------- */

if (!defined('LOGIN_TENTATIVAS_MAX')) {
    define('LOGIN_TENTATIVAS_MAX', 5);
}
if (!defined('LOGIN_TENTATIVAS_JANELA')) {
    define('LOGIN_TENTATIVAS_JANELA', 15 * 60); // 15 minutos
}

/**
 * Caminho do arquivo de contagem de falhas de login para e-mail+IP.
 */
if (!function_exists('login_arquivo_falhas')) {
    function login_arquivo_falhas($email, $ip)
    {
        $chave = sha1(strtolower($email) . '|' . $ip);
        return LOG_PATH . '/login-falhas-' . $chave . '.dat';
    }
}

/**
 * Lê as tentativas falhas registradas (timestamps) dentro da janela.
 *
 * @return array lista de timestamps
 */
if (!function_exists('login_ler_falhas')) {
    function login_ler_falhas($email, $ip)
    {
        $arquivo = login_arquivo_falhas($email, $ip);
        if (!is_file($arquivo)) {
            return array();
        }
        $conteudo = @file_get_contents($arquivo);
        if ($conteudo === false || $conteudo === '') {
            return array();
        }
        $agora = time();
        $falhas = array();
        foreach (explode(',', $conteudo) as $marca) {
            $marca = (int) $marca;
            if ($marca > 0 && ($agora - $marca) <= LOGIN_TENTATIVAS_JANELA) {
                $falhas[] = $marca;
            }
        }
        return $falhas;
    }
}

/**
 * Indica se o login está bloqueado (muitas falhas recentes).
 *
 * @return bool
 */
if (!function_exists('login_bloqueado')) {
    function login_bloqueado($email, $ip)
    {
        return count(login_ler_falhas($email, $ip)) >= LOGIN_TENTATIVAS_MAX;
    }
}

/**
 * Registra uma tentativa de login que falhou.
 */
if (!function_exists('login_registrar_falha')) {
    function login_registrar_falha($email, $ip)
    {
        $falhas = login_ler_falhas($email, $ip);
        $falhas[] = time();
        $arquivo = login_arquivo_falhas($email, $ip);
        @file_put_contents($arquivo, implode(',', $falhas), LOCK_EX);
    }
}

/**
 * Limpa as falhas após login bem-sucedido (ou quando o usuário reenvia
 * dados corretos).
 */
if (!function_exists('login_limpar_falhas')) {
    function login_limpar_falhas($email, $ip)
    {
        $arquivo = login_arquivo_falhas($email, $ip);
        if (is_file($arquivo)) {
            @unlink($arquivo);
        }
    }
}

/**
 * Retorna o IP da requisição atual (para contagem de tentativas).
 * Somente para rate limit local, não para auditoria de origem.
 *
 * @return string
 */
if (!function_exists('ip_requisicao')) {
    function ip_requisicao()
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        if (!is_string($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = '0.0.0.0';
        }
        return $ip;
    }
}
