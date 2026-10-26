<?php
/* ==========================================================
 * COMPONENTE: Login.php
 * Processa a autenticação do usuário e apresenta o formulário de login.
 * ========================================================== */

/**
 * Login.php — Processamento de login + tela
 */
require_once dirname(__FILE__) . '/../config/bootstrap.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_eventos.php';

$erroLogin = '';
$email = '';
// Aviso de sessão expirada (quem tentou acessar uma área sem sessão
// cai aqui; esta tela não inclui o header, por isso o consumo local).
$avisoLogin = aviso_sessao();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_exigir();

    $email = entrada_texto($_POST, 'email');
    $senha = isset($_POST['senha']) && is_string($_POST['senha']) ? $_POST['senha'] : '';

    if ($email === '' || $senha === '') {
        $erroLogin = 'Preencha e-mail e senha.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erroLogin = 'E-mail ou senha inválidos.';
    } elseif (login_bloqueado($email, ip_requisicao())) {
        registrar_log('Login bloqueado (força bruta) para e-mail informado. IP: ' . ip_requisicao(), 'WARNING');
        $erroLogin = 'Muitas tentativas falharam. Tente novamente em alguns minutos.';
    } else {
        $stmt = $conexao->prepare("SELECT id_usuario, nome, senha, tipo FROM usuario WHERE email = ? LIMIT 1");
        if ($stmt === false) {
            registrar_log('Falha ao preparar consulta de login: ' . $conexao->error, 'ERROR');
            $erroLogin = 'Não foi possível concluir o login agora. Tente novamente em instantes.';
        } else {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $usuario = $resultado->fetch_assoc();
            $stmt->close();

            if ($usuario && verificar_senha($senha, $usuario['senha'])) {
                login_limpar_falhas($email, ip_requisicao());

                // Login OK — cria sessão com novo ID (previne fixação de sessão)
                session_regenerate_id(true);
                // Rotaciona o token CSRF junto com o login.
                unset($_SESSION['csrf_token']);
                $_SESSION['id_usuario'] = (int) $usuario['id_usuario'];
                $_SESSION['nome_usuario'] = $usuario['nome'];
                $_SESSION['tipo_usuario'] = $usuario['tipo'];

                // Migração de senhas legadas (texto puro ou sha256) para bcrypt.
                if (senha_requer_atualizacao($usuario['senha'])) {
                    $novoHash = hash_senha($senha);
                    $stmtUpd = $conexao->prepare("UPDATE usuario SET senha = ? WHERE id_usuario = ?");
                    if ($stmtUpd !== false) {
                        $stmtUpd->bind_param('si', $novoHash, $usuario['id_usuario']);
                        $stmtUpd->execute();
                        $stmtUpd->close();
                    }
                }

                // O administrador cai direto no painel de controle do site.
                if ($usuario['tipo'] === 'admin') {
                    header('Location: PainelAdmin.php');
                    exit;
                }

                header('Location: CRUD_Eventos.php');
                exit;
            } else {
                login_registrar_falha($email, ip_requisicao());
                registrar_log('Falha de login. IP: ' . ip_requisicao(), 'WARNING');
                $erroLogin = 'E-mail ou senha inválidos.';
            }
        }
    }
}

// Se já logado, redireciona para o painel
if (isset($_SESSION['id_usuario'])) {
    if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin') {
        header('Location: PainelAdmin.php');
    } else {
        header('Location: CRUD_Eventos.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <script>document.documentElement.classList.add('js');</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventoVivo — Entrar na sua conta</title>
    <meta name="description" content="Entre na sua conta EventoVivo e acesse artistas, eventos e a cena independente da sua cidade.">
    <meta name="theme-color" content="#120f0f">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' fill='%23120f0f'/%3E%3Ctext x='32' y='46' font-family='Arial, sans-serif' font-weight='900' font-size='38' fill='%23b3121e' text-anchor='middle'%3EV%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Courier+Prime:wght@400;700&family=Permanent+Marker&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Css/tokens.css">
    <link rel="stylesheet" href="../Css/reset.css">
    <link rel="stylesheet" href="../Css/layout.css">
    <link rel="stylesheet" href="../Css/components.css">
    <link rel="stylesheet" href="../Css/site.css">
    <link rel="stylesheet" href="../Css/auth.css">
    <script src="Js/menu.js" defer></script>
</head>
<body>


<div class="auth-topo">
  <a href="Home.php">&larr; Voltar para Home</a>
</div>

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

        <?php if ($avisoLogin !== ''): ?>
          <div class="alert alert-atencao">
            <?php echo htmlspecialchars($avisoLogin); ?>
          </div>
        <?php endif; ?>

        <?php if ($erroLogin !== ''): ?>
          <div class="alert alert-erro">
            <?php echo htmlspecialchars($erroLogin); ?>
          </div>
        <?php endif; ?>

        <form action="Login.php" method="post" autocomplete="on" data-loading="true">
        <?php echo csrf_campo(); ?>
        <div class="form-group">
          <label for="email">E-mail</label>
          <input type="email" id="email" name="email" placeholder="seu@email.com" required
                 value="<?php echo htmlspecialchars($email); ?>">
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

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>
