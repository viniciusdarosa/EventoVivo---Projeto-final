<?php
/* ==========================================================
 * COMPONENTE: header.php
 * Componente reutilizável de cabeçalho. Monta o <head> com as
 * folhas globais, a navegação principal (com menu móvel
 * acessível) e os linkes condicionais de acordo com o estado
 * da sessão.
 * ========================================================== */

// A sessão é iniciada pelo config/bootstrap.php (com opções seguras).
// Este componente só deve ser incluído depois do bootstrap.
if (!defined('EVENTOVIVO_BOOTSTRAP')) {
    exit('Componente header.php deve ser incluído após config/bootstrap.php.');
}

// Variáveis opcionais que a tela pode definir antes de incluir este componente.
if (!isset($pageTitle)) {
    $pageTitle = 'EventoVivo — Divulgacao de Artistas e Eventos Independentes';
}
if (!isset($pageDescription)) {
    $pageDescription = 'EventoVivo conecta artistas independentes e publico: divulgue seu trabalho, publique eventos e descubra a cena da sua cidade.';
}
if (!isset($extraCss) || !is_array($extraCss)) {
    $extraCss = array();
}
if (!isset($extraStyle)) {
    $extraStyle = '';
}

// O link do painel só aparece para sessões marcadas como admin no login.
$ehAdministrador = isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';
// REGRA DE PUBLICAÇÃO (mesma do servidor): só 'empresa' e 'admin'
// criam eventos — o link "+ Novo Evento" reflete isso na interface.
$podePublicar = isset($_SESSION['tipo_usuario'])
    && ($_SESSION['tipo_usuario'] === 'empresa' || $_SESSION['tipo_usuario'] === 'admin');
// Aviso de sessão expirada gravado pelo bootstrap (consumido uma vez só).
$avisoExpiradoHeader = aviso_sessao();
// Feedbacks de PRG (flash_definir) — consumidos AQUI, uma única vez,
// para toda página que use o cabeçalho (inclusive as de listagem).
$flashMensagensHeader = flash_ler();

// Página atual (para aria-current no menu).
$paginaAtual = basename(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '');

/**
 * Marca o link da página aberta com aria-current="page".
 */
function header_link($href, $rotulo)
{
    global $paginaAtual;
    $atual = (basename($href) === $paginaAtual) ? ' aria-current="page"' : '';
    echo '<a href="' . htmlspecialchars($href) . '"' . $atual . '>' . htmlspecialchars($rotulo) . '</a>';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <script>document.documentElement.classList.add('js');</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#120f0f">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' fill='%23120f0f'/%3E%3Ctext x='32' y='46' font-family='Arial, sans-serif' font-weight='900' font-size='38' fill='%23b3121e' text-anchor='middle'%3EV%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Courier+Prime:wght@400;700&family=Permanent+Marker&display=swap" rel="stylesheet">
    <!-- Folhas globais (ordem importa) -->
    <link rel="stylesheet" href="../Css/tokens.css">
    <link rel="stylesheet" href="../Css/reset.css">
    <link rel="stylesheet" href="../Css/layout.css">
    <link rel="stylesheet" href="../Css/components.css">
    <link rel="stylesheet" href="../Css/site.css">
    <!-- Folhas específicas da página -->
    <?php foreach ($extraCss as $folha): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($folha); ?>">
    <?php endforeach; ?>
    <?php if (trim($extraStyle) !== ''): ?>
        <style>
<?php echo $extraStyle; ?>
        </style>
    <?php endif; ?>
    <script src="Js/menu.js" defer></script>
</head>
<body>

<a class="skip-link" href="#conteudo">Pular para o conteúdo</a>

<header class="site-header">
  <div class="wrap header-inner">
    <a class="logo" href="Home.php">Evento<span>Vivo</span></a>

    <button class="menu-toggle" type="button"
            aria-expanded="false" aria-controls="nav-principal"
            aria-label="Abrir menu de navegação">
      <span class="menu-toggle-barra" aria-hidden="true"></span>
      <span class="menu-toggle-barra" aria-hidden="true"></span>
      <span class="menu-toggle-barra" aria-hidden="true"></span>
    </button>

    <nav class="nav" id="nav-principal" aria-label="Navegação principal">
      <?php header_link('Home.php', 'Home'); ?>
      <?php header_link('Eventos.php', 'Eventos'); ?>
      <?php header_link('Artistas.php', 'Artistas'); ?>

      <form class="nav-busca" action="Busca.php" method="get" role="search">
        <label for="nav-q" class="visually-hidden">Buscar no site</label>
        <input id="nav-q" type="search" name="q" placeholder="Buscar..."
               maxlength="100"
               value="<?php echo htmlspecialchars(entrada_texto($_GET, 'q')); ?>">
        <button type="submit">Buscar</button>
      </form>

      <div class="nav-conta">
        <?php if (isset($_SESSION['id_usuario'])): ?>
          <?php if ($ehAdministrador): ?>
            <?php header_link('PainelAdmin.php', 'Painel ADM'); ?>
          <?php endif; ?>
          <?php header_link('CRUD_Eventos.php', 'Meus Eventos'); ?>
          <?php if ($podePublicar): ?>
            <?php header_link('CadastrarEvento.php', '+ Novo Evento'); ?>
          <?php endif; ?>
          <?php header_link('CRUD_Freelancers.php', 'Meu Perfil Artista'); ?>
          <?php header_link('MeusFavoritos.php', 'Favoritos'); ?>
          <span class="header-user">
            Olá, <?php echo htmlspecialchars($_SESSION['nome_usuario']); ?>
          </span>
          <form action="Logout.php" method="post">
            <?php echo csrf_campo(); ?>
            <button type="submit" class="header-logout">Sair</button>
          </form>
        <?php else: ?>
          <?php header_link('Login.php', 'Entrar'); ?>
          <?php header_link('Cadastro.php', 'Cadastrar'); ?>
        <?php endif; ?>
      </div>
    </nav>
  </div>
</header>

<?php if ($avisoExpiradoHeader !== '' || !empty($flashMensagensHeader)): ?>
  <div class="wrap wrap-alertas">
    <?php if ($avisoExpiradoHeader !== ''): ?>
      <div class="alert alert-erro">
        <?php echo htmlspecialchars($avisoExpiradoHeader); ?>
        <a href="Login.php">Entrar novamente</a>.
      </div>
    <?php endif; ?>
    <?php foreach ($flashMensagensHeader as $flashMensagemHeader): ?>
      <div class="alert <?php echo ($flashMensagemHeader['nivel'] === 'erro') ? 'alert-erro' : 'alert-sucesso'; ?>">
        <?php echo htmlspecialchars($flashMensagemHeader['texto']); ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
