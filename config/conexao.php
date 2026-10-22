<?php
/* ==========================================================
 * COMPONENTE: conexao.php
 * Configuração central da conexão MySQL.
 *
 * As credenciais NÃO ficam fixas no código. A ordem de leitura é:
 *   1. Variáveis de ambiente EVENTOVIVO_DB_HOST/_USER/_PASS/_NAME
 *      (recomendado em produção — ex.: Export no painel do servidor).
 *   2. Arquivo config/credenciais.local.php (fora do controle de versão;
 *      copie de credenciais.exemplo.php).
 *   3. Padrões locais de desenvolvimento (fácil no EasyPHP).
 *
 * Em caso de falha, o detalhe técnico vai apenas para o log; o usuário
 * vê uma mensagem genérica (nunca credenciais ou erros do banco).
 * ========================================================== */

$configLocal = array(
    'host' => 'localhost',
    'usuario' => 'root',
    'senha' => '',
    'banco' => 'eventovivo',
);

$arquivoLocal = dirname(__FILE__) . '/credenciais.local.php';
if (is_file($arquivoLocal)) {
    $carregado = include $arquivoLocal;
    if (is_array($carregado)) {
        $configLocal = array_merge($configLocal, $carregado);
    }
}

$mapeamentoEnv = array(
    'host' => 'EVENTOVIVO_DB_HOST',
    'usuario' => 'EVENTOVIVO_DB_USER',
    'senha' => 'EVENTOVIVO_DB_PASS',
    'banco' => 'EVENTOVIVO_DB_NAME',
);
foreach ($mapeamentoEnv as $chave => $variavel) {
    $valorEnv = getenv($variavel);
    if ($valorEnv !== false && $valorEnv !== '') {
        $configLocal[$chave] = $valorEnv;
    }
}

$servidor = $configLocal['host'];
$usuario = $configLocal['usuario'];
$senha = $configLocal['senha'];
$banco = $configLocal['banco'];

$conexao = @new mysqli($servidor, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    // Detalhe técnico somente no log (nunca na tela).
    if (function_exists('registrar_log')) {
        registrar_log(
            'Falha na conexao MySQL (' . $servidor . '/' . $banco . '): ' . $conexao->connect_error,
            'ERROR'
        );
    }
    if (php_sapi_name() === 'cli') {
        fwrite(STDERR, "Erro na conexao com o banco de dados. Veja logs/app.log.\n");
        exit(1);
    }
    http_response_code(500);
    exit('Serviço temporariamente indisponível. Tente novamente em instantes.');
}

// Garante UTF-8 em toda a comunicação com o banco
$conexao->set_charset('utf8mb4');
