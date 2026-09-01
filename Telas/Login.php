<?php
/**
 * Login.php — Processamento de login + tela
 * Compatível com PHP 5.3.9 (sem short arrays, ??, __DIR__, etc.)
 */
session_start();
require_once dirname(__FILE__) . '/../config/conexao.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_eventos.php';

$erroLogin = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';

    if ($email === '' || $senha === '') {
        $erroLogin = 'Preencha e-mail e senha.';
    } else {
        $stmt = $conexao->prepare("SELECT id_usuario, nome, senha FROM usuario WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();

        if ($usuario && verificar_senha($senha, $usuario['senha'])) {
            // Login OK — cria sessão
            session_regenerate_id(true);
            $_SESSION['id_usuario'] = (int) $usuario['id_usuario'];
            $_SESSION['nome_usuario'] = $usuario['nome'];
            header('Location: CRUD_Eventos.php');
            exit;
        } else {
            $erroLogin = 'E-mail ou senha inválidos.';
        }
    }
}

// Se já logado, redireciona para o painel
if (isset($_SESSION['id_usuario'])) {
    header('Location: CRUD_Eventos.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventoVivo — Entrar na sua conta</title>
    <meta name="description" content="Entre na sua conta EventoVivo e acesse artistas, eventos e a cena independente da sua cidade.">
    <meta name="theme-color" content="#110f0c">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' fill='%23110f0c'/%3E%3Ctext x='32' y='46' font-family='Arial, sans-serif' font-weight='900' font-size='38' fill='%23b8000d' text-anchor='middle'%3EV%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Courier+Prime:wght@400;700&family=Permanent+Marker&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body>

<main class="auth-split">

  <div class="auth-brand">
    <p class="brand-logo">Evento<span>Vivo</span></p>
    <h1 class="brand-headline">A cena independente tem endereço.</h1>
    <p class="brand-sub">Entre na sua conta, divulgue seus shows, conecte-se com o público e faça parte da rede de artistas que move a cena fora do eixo.</p>
    <p class="brand-accent">Sem gravadora. Sem empresário. Sem filtro.</p>
  </div>

  <div class="auth-form-panel">
<div class="auth-card">
        <h2>Entrar</h2>

        <?php if ($erroLogin !== ''): ?>
          <div class="alert alert-erro" style="margin-bottom:1rem;padding:.8rem;background:rgba(179,18,30,.12);border:2px solid #b3121e;color:#e6dfc9;font-size:.85rem;">
            <?php echo htmlspecialchars($erroLogin); ?>
          </div>
        <?php endif; ?>

        <form action="Login.php" method="post" autocomplete="on">
        <div class="form-group">
          <label for="email">E-mail</label>
          <input type="email" id="email" name="email" placeholder="seu@email.com" required>
        </div>
        <div class="form-group">
          <label for="senha">Senha</label>
          <input type="password" id="senha" name="senha" placeholder="Sua senha" required>
        </div>
        <button type="submit" class="btn-auth">Entrar</button>
      </form>

      <div class="auth-divider">ou</div>

      <p class="auth-switch">
        Ainda não tem conta? <a href="Cadastro.php">Cadastre-se</a>
      </p>
    </div>
  </div>

</main>

</body>
</html>
