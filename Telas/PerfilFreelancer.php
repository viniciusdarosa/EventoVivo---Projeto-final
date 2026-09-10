<?php
/**
 * PerfilFreelancer.php — Perfil público de um freelancer/artista
 * Compatível com PHP 5.3.9
 */
session_start();
require_once dirname(__FILE__) . '/../config/conexao.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_freelancers.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_eventos.php';

// ID do freelancer vem da URL
$idFreelancer = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;

if ($idFreelancer <= 0) {
    header('Location: Artistas.php');
    exit;
}

// Busca dados completos do freelancer
$freelancer = buscar_freelancer_completo($conexao, $idFreelancer);

if (!$freelancer) {
    header('Location: Artistas.php');
    exit;
}

// Busca portfolio
$portfolio = buscar_portfolio_freelancer($conexao, $idFreelancer);

// Busca avaliações
$avaliacoes = buscar_avaliacoes_freelancer($conexao, $idFreelancer);
$mediaAvaliacoes = calcular_media_avaliacoes($conexao, $idFreelancer);

// Verifica se o usuário logado é o dono do perfil
$ehDono = isset($_SESSION['id_usuario']) && $_SESSION['id_usuario'] == $freelancer['usuario_id'];

// Verifica se já avaliou (para não permitir avaliação duplicada)
$jaAvaliou = false;
if (isset($_SESSION['id_usuario']) && !$ehDono) {
    $stmt = $conexao->prepare("SELECT id FROM avaliacoes WHERE avaliador = ? AND freelancer_id = ? LIMIT 1");
    $stmt->bind_param('ii', $_SESSION['id_usuario'], $idFreelancer);
    $stmt->execute();
    $stmt->store_result();
    $jaAvaliou = $stmt->num_rows > 0;
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($freelancer['nome']); ?> — EventoVivo</title>
  <meta name="description" content="<?php echo htmlspecialchars(substr($freelancer['descricao'], 0, 160)); ?>">
  <meta name="theme-color" content="#110f0c">
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' fill='%23110f0c'/%3E%3Ctext x='32' y='46' font-family='Arial, sans-serif' font-weight='900' font-size='38' fill='%23b8000d' text-anchor='middle'%3EV%3C/text%3E%3C/svg%3E">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Courier+Prime:wght@400;700&family=Permanent+Marker&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../Css/style.css">
  <link rel="stylesheet" href="../Css/crud_eventos.css">
  <style>
    .perfil-hero {
      position: relative;
      padding: 4rem 0 3rem;
      background: linear-gradient(135deg, var(--ink) 0%, var(--ink-2) 50%, var(--ink) 100%);
      border-bottom: 1px solid var(--rule);
    }
    .perfil-hero::after {
      content: "";
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 3px;
      background: linear-gradient(90deg, var(--blood) 0%, var(--accent) 40%, transparent 100%);
    }
    .perfil-capa {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0.15;
      z-index: 0;
    }
    .perfil-header {
      position: relative;
      z-index: 1;
      display: flex;
      gap: 2rem;
      flex-wrap: wrap;
      align-items: flex-end;
    }
    .perfil-foto {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid var(--blood);
      box-shadow: var(--shadow-hard);
      background: var(--steel);
    }
    .perfil-info h1 {
      font-family: var(--font-display);
      text-transform: uppercase;
      font-size: clamp(2rem, 5vw, 3rem);
      line-height: 1.1;
      margin-bottom: .5rem;
    }
    .perfil-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 1.5rem;
      margin-top: 1rem;
      color: var(--paper-dim);
      font-size: .9rem;
    }
    .perfil-meta span { display: flex; align-items: center; gap: .35rem; }
    .perfil-categoria {
      display: inline-block;
      background: rgba(184,0,13,.15);
      border: 1px solid var(--accent);
      color: var(--accent);
      padding: .25rem .75rem;
      font-size: .7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .06em;
      margin-top: .5rem;
    }
    .perfil-valor {
      color: var(--yellow);
      font-family: var(--font-mark);
      font-size: 1.1rem;
      font-weight: 700;
    }
    .perfil-contatos {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-top: 1.5rem;
    }
    .btn-contato {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      padding: .7rem 1.2rem;
      background: var(--blood);
      color: var(--paper);
      border: 2px solid var(--blood);
      font-weight: 700;
      text-transform: uppercase;
      font-size: .8rem;
      letter-spacing: .05em;
      transition: background var(--ease), color var(--ease);
    }
    .btn-contato:hover { background: transparent; color: var(--blood); }
    .btn-contato.ghost { background: transparent; border-color: var(--rule); color: var(--paper); }
    .btn-contato.ghost:hover { border-color: var(--accent); color: var(--accent); }

    .perfil-section { padding: 3rem 0; }
    .perfil-section.alt { background: var(--ink-2); border-top: 1px solid var(--rule); }
    .section-title {
      font-family: var(--font-display);
      text-transform: uppercase;
      font-size: clamp(1.5rem, 3vw, 2rem);
      border-bottom: 3px solid var(--accent);
      padding-bottom: .5rem;
      margin-bottom: 2rem;
      display: inline-block;
    }

    .descricao-texto {
      color: var(--paper-dim);
      line-height: 1.8;
      font-size: 1rem;
      max-width: 80ch;
    }

    .experiencia-lista {
      list-style: disc;
      padding-left: 1.5rem;
      color: var(--paper-dim);
      line-height: 1.8;
    }
    .experiencia-lista li { margin-bottom: .5rem; }

    .portfolio-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 1.5rem;
    }
    .portfolio-item {
      background: var(--steel);
      border: 2px solid var(--rule);
      box-shadow: var(--shadow-hard);
      overflow: hidden;
      transition: transform var(--ease), border-color var(--ease), box-shadow var(--ease);
    }
    .portfolio-item:hover {
      transform: translateY(-2px);
      border-color: var(--accent);
      box-shadow: 8px 8px 0 rgba(0,0,0,.55);
    }
    .portfolio-img {
      width: 100%;
      aspect-ratio: 4/3;
      object-fit: cover;
      background: var(--ink-2);
    }
    .portfolio-info { padding: 1rem; }
    .portfolio-info h4 { font-weight: 700; text-transform: uppercase; font-size: .9rem; margin-bottom: .35rem; }
    .portfolio-info p { color: var(--paper-dim); font-size: .8rem; line-height: 1.5; }

    .avaliacoes-lista { display: flex; flex-direction: column; gap: 1.5rem; }
    .avaliacao-card {
      background: var(--steel);
      border: 2px solid var(--rule);
      box-shadow: var(--shadow-hard);
      padding: 1.5rem;
    }
    .avaliacao-header { display: flex; align-items: center; gap: 1rem; margin-bottom: .75rem; }
    .avaliador-foto {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid var(--rule);
      background: var(--ink-2);
    }
    .avaliador-info { flex: 1; }
    .avaliador-nome { font-weight: 700; text-transform: uppercase; font-size: .9rem; }
    .avaliacao-data { color: var(--paper-dim); font-size: .75rem; }
    .avaliacao-estrelas { color: var(--yellow); font-family: var(--font-mark); font-size: 1.1rem; letter-spacing: .1em; }
    .avaliacao-comentario { color: var(--paper-dim); line-height: 1.6; }

    .avaliar-form {
      background: var(--steel);
      border: 2px solid var(--rule);
      box-shadow: var(--shadow-hard);
      padding: 2rem;
      max-width: 600px;
    }
    .avaliar-form .campo { margin-bottom: 1rem; }
    .avaliar-form label { display: block; margin-bottom: .35rem; font-weight: 700; text-transform: uppercase; font-size: .75rem; color: var(--paper-dim); letter-spacing: .05em; }
    .estrelas-input { display: flex; gap: .5rem; direction: rtl; }
    .estrelas-input input { display: none; }
    .estrelas-input label {
      font-size: 2rem;
      color: var(--rule);
      cursor: pointer;
      transition: color var(--ease);
    }
    .estrelas-input input:checked ~ label,
    .estrelas-input label:hover,
    .estrelas-input label:hover ~ label { color: var(--yellow); }
    .avaliar-form textarea {
      width: 100%;
      min-height: 100px;
      background: var(--ink);
      border: 2px solid var(--rule);
      color: var(--paper);
      padding: .75rem;
      font-family: var(--font-body);
      font-size: .9rem;
      resize: vertical;
    }
    .avaliar-form textarea:focus { outline: none; border-color: var(--accent); }

    .sem-avaliacao { color: var(--paper-dim); font-style: italic; }
    .estrelas-avaliacao { display: flex; align-items: center; gap: .5rem; }
    .estrela { font-size: 1.2rem; }
    .estrela.cheia { color: var(--yellow); }
    .estrela.meia { color: var(--yellow); opacity: .5; }
    .estrela.vazia { color: var(--rule); }
    .media-numero { color: var(--paper-dim); font-size: .9rem; }

    @media (max-width: 720px) {
      .perfil-header { flex-direction: column; align-items: center; text-align: center; }
      .perfil-meta { justify-content: center; }
      .perfil-contatos { justify-content: center; }
      .avaliar-form { padding: 1.5rem; }
    }
  </style>
</head>
<?php require dirname(__FILE__) . '/Componentes/header.php'; ?>
<body>

<main class="perfil-page">
  <!-- Hero com foto de capa -->
  <section class="perfil-hero">
    <?php if (!empty($freelancer['portfolio'])): ?>
      <img class="perfil-capa" src="<?php echo htmlspecialchars(freelancer_portfolio_src($freelancer['portfolio'])); ?>" alt="">
    <?php endif; ?>
    <div class="wrap">
      <div class="perfil-header">
        <img class="perfil-foto" src="<?php echo htmlspecialchars(freelancer_foto_src($freelancer['foto_perfil'])); ?>" alt="Foto de <?php echo htmlspecialchars($freelancer['nome']); ?>">

        <div class="perfil-info">
          <h1><?php echo htmlspecialchars($freelancer['nome']); ?></h1>

          <div class="perfil-meta">
            <span class="perfil-valor"><?php echo formatar_valor_freelancer($freelancer['valor_hora']); ?></span>
            <span><?php echo htmlspecialchars($freelancer['cidade'] . '/' . $freelancer['estado']); ?></span>
          </div>

          <span class="perfil-categoria"><?php echo htmlspecialchars($freelancer['categoria_nome']); ?></span>

          <div class="perfil-contatos">
            <?php if (!empty($freelancer['email'])): ?>
              <a href="mailto:<?php echo htmlspecialchars($freelancer['email']); ?>" class="btn-contato">✉ Email</a>
            <?php endif; ?>
            <?php if (!empty($freelancer['telefone'])): ?>
              <a href="tel:<?php echo htmlspecialchars(preg_replace('/\D/', '', $freelancer['telefone'])); ?>" class="btn-contato">📞 WhatsApp</a>
            <?php endif; ?>
            <?php if (!empty($freelancer['rede_social'])): ?>
              <a href="https://instagram.com/<?php echo ltrim($freelancer['rede_social'], '@'); ?>" target="_blank" class="btn-contato ghost">📸 Instagram</a>
            <?php endif; ?>
            <?php if ($ehDono): ?>
              <a href="EditarFreelancer.php" class="btn-contato ghost">✎ Editar Perfil</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Sobre / Descrição -->
  <section class="perfil-section">
    <div class="wrap">
      <h2 class="section-title">Sobre</h2>
      <div class="descricao-texto">
        <?php echo nl2br(htmlspecialchars($freelancer['descricao'])); ?>
      </div>

      <?php if (!empty($freelancer['experiencia'])): ?>
        <h3 style="margin-top:2rem;font-family:var(--font-body);font-size:1.1rem;text-transform:uppercase;letter-spacing:.02em;">Experiência</h3>
        <ul class="experiencia-lista">
          <?php
          $expLinhas = explode("\n", $freelancer['experiencia']);
          foreach ($expLinhas as $linha) {
              $linha = trim($linha);
              if ($linha !== '') {
                  echo '<li>' . htmlspecialchars($linha) . '</li>';
              }
          }
          ?>
        </ul>
      <?php endif; ?>
    </div>
  </section>

  <!-- Portfolio -->
  <?php if (!empty($portfolio)): ?>
  <section class="perfil-section alt">
    <div class="wrap">
      <h2 class="section-title">Portfolio</h2>
      <div class="portfolio-grid">
        <?php foreach ($portfolio as $item): ?>
          <article class="portfolio-item">
            <img class="portfolio-img" src="<?php echo htmlspecialchars(freelancer_portfolio_src($item['imagem'])); ?>" alt="<?php echo htmlspecialchars($item['descricao']); ?>">
            <div class="portfolio-info">
              <?php if (!empty($item['descricao'])): ?>
                <h4><?php echo htmlspecialchars($item['descricao']); ?></h4>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Avaliações -->
  <section class="perfil-section">
    <div class="wrap">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;">
        <h2 class="section-title" style="margin-bottom:0;">Avaliações</h2>
        <div class="estrelas-avaliacao" style="font-size:1.5rem;">
          <?php echo render_estrelas_avaliacao($mediaAvaliacoes); ?>
        </div>
      </div>

      <?php if (!empty($avaliacoes)): ?>
        <div class="avaliacoes-lista">
          <?php foreach ($avaliacoes as $av): ?>
            <article class="avaliacao-card">
              <div class="avaliacao-header">
                <img class="avaliador-foto" src="<?php echo htmlspecialchars(freelancer_foto_src($av['avaliador_foto'])); ?>" alt="<?php echo htmlspecialchars($av['avaliador_nome']); ?>">
                <div class="avaliador-info">
                  <div class="avaliador-nome"><?php echo htmlspecialchars($av['avaliador_nome']); ?></div>
                  <div class="avaliacao-data"><?php echo date('d/m/Y H:i', strtotime($av['data_avaliacao'])); ?></div>
                </div>
                <div class="avaliacao-estrelas">
                  <?php
                  for ($i = 1; $i <= 5; $i++) {
                      echo $i <= $av['nota'] ? '★' : '☆';
                  }
                  ?>
                </div>
              </div>
              <?php if (!empty($av['comentario'])): ?>
                <div class="avaliacao-comentario"><?php echo htmlspecialchars($av['comentario']); ?></div>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="sem-avaliacao">Este artista ainda não possui avaliações.</p>
      <?php endif; ?>

      <!-- Formulário de avaliação (apenas usuários logados que não são o dono e não avaliaram) -->
      <?php if (isset($_SESSION['id_usuario']) && !$ehDono && !$jaAvaliou): ?>
        <div style="margin-top:3rem;">
          <h3 style="font-family:var(--font-body);font-size:1.1rem;text-transform:uppercase;letter-spacing:.02em;margin-bottom:1rem;">Deixe sua avaliação</h3>
          <form class="avaliar-form" action="AvaliarFreelancer.php" method="post">
            <input type="hidden" name="freelancer_id" value="<?php echo $idFreelancer; ?>">
            <input type="hidden" name="redirect" value="PerfilFreelancer.php?id=<?php echo $idFreelancer; ?>">

            <div class="campo">
              <label>Sua nota</label>
              <div class="estrelas-input" style="direction:ltr;">
                <input type="radio" id="estrela5" name="nota" value="5" required><label for="estrela5" title="5 estrelas">★</label>
                <input type="radio" id="estrela4" name="nota" value="4"><label for="estrela4" title="4 estrelas">★</label>
                <input type="radio" id="estrela3" name="nota" value="3"><label for="estrela3" title="3 estrelas">★</label>
                <input type="radio" id="estrela2" name="nota" value="2"><label for="estrela2" title="2 estrelas">★</label>
                <input type="radio" id="estrela1" name="nota" value="1"><label for="estrela1" title="1 estrela">★</label>
              </div>
            </div>

            <div class="campo">
              <label for="comentario">Comentário (opcional)</label>
              <textarea id="comentario" name="comentario" placeholder="O que você achou do trabalho deste artista?"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">ENVIAR AVALIAÇÃO</button>
          </form>
        </div>
      <?php elseif (isset($_SESSION['id_usuario']) && $jaAvaliou): ?>
        <p style="margin-top:2rem;color:var(--paper-dim);">Você já avaliou este artista.</p>
      <?php elseif (!isset($_SESSION['id_usuario'])): ?>
        <p style="margin-top:2rem;color:var(--paper-dim);"><a href="Login.php" style="color:var(--accent);">Faça login</a> para avaliar este artista.</p>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>
</body>
</html>