<?php
/* ==========================================================
 * COMPONENTE: Logout.php
 * Encerra a sessão do usuário e redireciona para a página inicial.
 * Aceita apenas POST com token CSRF — evita encerramento de sessão
 * por meio de link malicioso (logout via CSRF).
 * ========================================================== */

/**
 * Logout.php — Destrói a sessão e redireciona para Home
 */
require_once dirname(__FILE__) . '/../config/bootstrap.php';

// Apenas POST com token válido (o link do header envia um form POST).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('Home.php');
}
csrf_exigir();

// Destrói todos os dados da sessão
$_SESSION = array();

// Se usar cookies de sessão, delete o cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão (apenas se houver sessão ativa)
if (session_id() !== '') {
    session_destroy();
}

// Redireciona para Home
redirecionar('Home.php');
