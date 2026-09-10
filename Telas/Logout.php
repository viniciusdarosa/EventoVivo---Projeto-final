<?php
/* ==========================================================
 * COMPONENTE: Logout.php
 * Encerra a sessão do usuário e redireciona para a página inicial.
 * ========================================================== */

/**
 * Logout.php — Destrói a sessão e redireciona para Home
 */
session_start();

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

// Destrói a sessão
session_destroy();

// Redireciona para Home
header('Location: Home.php');
exit;