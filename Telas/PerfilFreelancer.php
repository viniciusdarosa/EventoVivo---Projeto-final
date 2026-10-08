<?php
/* ==========================================================
 * COMPONENTE: PerfilFreelancer.php
 * Apresenta o perfil público completo de um freelancer, incluindo dados profissionais, portfolio e avaliações.
 * ========================================================== */

/**
 * PerfilFreelancer.php — Perfil público de um freelancer/artista
 */
require_once dirname(__FILE__) . '/../config/bootstrap.php';
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

// Busca fotos do carrossel do trabalho
$fotosCarrossel = buscar_carrossel_freelancer($conexao, $idFreelancer);
$totalCarrossel = count($fotosCarrossel);

// Busca avaliações
$avaliacoes = buscar_avaliacoes_freelancer($conexao, $idFreelancer);
$mediaAvaliacoes = calcular_media_avaliacoes($conexao, $idFreelancer);

// Verifica se o usuário logado é o dono do perfil
$ehDono = isset($_SESSION['id_usuario']) && $_SESSION['id_usuario'] == $freelancer['usuario_id'];

// Verifica se já avaliou
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

    /* ---- Carrossel de fotos do trabalho ---- */
    .carrossel {
      position: relative;
      background: var(--steel);
      border: 2px solid var(--rule);
      box-shadow: var(--shadow-hard);
    }
    .carrossel-viewport {
      position: relative;
      width: 100%;
      overflow: hidden;
      background: var(--ink-2);
    }
    .carrossel-trilha {
      display: flex;
      transition: transform .45s ease;
      will-change: transform;
    }
    .carrossel-slide {
      flex: 0 0 100%;
      min-width: 100%;
      position: relative;
    }
    .carrossel-slide img {
      display: block;
      width: 100%;
      aspect-ratio: 3 / 2;
      max-height: 66vh;
      object-fit: cover;
      object-position: center 30%;
      background: var(--ink-2);
    }
    .carrossel-legenda {
      position: absolute;
      left: 0;
      bottom: 0;
      width: 100%;
      padding: 2.5rem 1.25rem .9rem;
      background: linear-gradient(to top, rgba(0,0,0,.88) 0%, rgba(0,0,0,.55) 55%, transparent 100%);
      color: var(--paper);
      font-family: var(--font-body);
      font-size: .9rem;
      line-height: 1.5;
      margin: 0;
    }
    .carrossel-seta {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      z-index: 3;
      width: 44px;
      height: 44px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(18,15,15,.82);
      border: 2px solid var(--rule);
      color: var(--paper);
      font-size: 1.3rem;
      line-height: 1;
      cursor: pointer;
      padding: 0;
      transition: background var(--ease), border-color var(--ease), color var(--ease);
    }
    .carrossel-seta:hover {
      background: var(--blood);
      border-color: var(--blood);
      color: var(--paper);
    }
    .carrossel-seta:focus-visible { outline: 2px solid var(--yellow); outline-offset: 2px; }
    .carrossel-seta.anterior { left: .75rem; }
    .carrossel-seta.proximo { right: .75rem; }
    .carrossel-seta[disabled] { opacity: 0; pointer-events: none; }

    .carrossel-rodape {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      padding: .75rem 1rem;
      border-top: 1px solid var(--rule);
      flex-wrap: wrap;
    }
    .carrossel-dots {
      display: flex;
      gap: .5rem;
      flex-wrap: wrap;
      margin: 0;
      padding: 0;
      list-style: none;
    }
    .carrossel-dot {
      display: block;
      width: 11px;
      height: 11px;
      padding: 0;
      background: transparent;
      border: 2px solid var(--rule);
      cursor: pointer;
      transition: background var(--ease), border-color var(--ease);
    }
    .carrossel-dot:hover { border-color: var(--paper-dim); }
    .carrossel-dot[aria-current="true"] { background: var(--accent); border-color: var(--accent); }
    .carrossel-dot:focus-visible { outline: 2px solid var(--yellow); outline-offset: 2px; }
    .carrossel-contador {
      color: var(--paper-dim);
      font-family: var(--font-body);
      font-size: .8rem;
      letter-spacing: .08em;
      margin-left: auto;
    }

    .carrossel-vazio {
      background: var(--steel);
      border: 2px dashed var(--rule);
      padding: 2.5rem 1.5rem;
      text-align: center;
      color: var(--paper-dim);
    }
    .carrossel-gestao { margin-top: 1.25rem; }

    /* Abas de avaliações */
    .abas-avaliacoes {
      display: flex;
      gap: .5rem;
      margin-bottom: 1.5rem;
      border-bottom: 2px solid var(--rule);
      flex-wrap: wrap;
    }
    .aba-btn {
      background: transparent;
      border: none;
      color: var(--paper-dim);
      font-family: var(--font-body);
      font-size: .9rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .05em;
      padding: .75rem 1.5rem;
      cursor: pointer;
      border-bottom: 3px solid transparent;
      margin-bottom: -2px;
      transition: color var(--ease), border-color var(--ease);
    }
    .aba-btn:hover { color: var(--accent); }
    .aba-btn.ativa {
      color: var(--accent);
      border-bottom-color: var(--accent);
    }
    .aba-conteudo { display: none; }
    .aba-conteudo.ativa { display: block; animation: fadeIn .2s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

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
      .abas-avaliacoes { justify-content: center; }
      .carrossel-seta { width: 38px; height: 38px; font-size: 1.1rem; }
      .carrossel-seta.anterior { left: .4rem; }
      .carrossel-seta.proximo { right: .4rem; }
      .carrossel-legenda { font-size: .8rem; padding: 2rem .75rem .7rem; }
      .carrossel-contador { margin-left: 0; }
    }
    @media (max-width: 480px) {
      .carrossel-rodape { justify-content: center; }
      .carrossel-contador { width: 100%; text-align: center; order: -1; }
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

  <!-- Carrossel de fotos do trabalho -->
  <?php if ($totalCarrossel > 0 || $ehDono): ?>
  <section class="perfil-section alt">
    <div class="wrap">
      <h2 class="section-title">Trabalhos</h2>

      <?php if ($totalCarrossel > 0): ?>
        <div class="carrossel" id="carrossel" role="region" aria-roledescription="carrossel" aria-label="Fotos do trabalho de <?php echo htmlspecialchars($freelancer['nome']); ?>" tabindex="0">
          <div class="carrossel-viewport">
            <div class="carrossel-trilha" id="carrossel-trilha">
              <?php foreach ($fotosCarrossel as $indice => $foto): ?>
                <figure class="carrossel-slide" role="group" aria-roledescription="slide"
                        aria-label="<?php echo $indice + 1; ?> de <?php echo $totalCarrossel; ?>"
                        <?php if ($indice > 0): ?>aria-hidden="true"<?php endif; ?>>
                  <img src="<?php echo htmlspecialchars(freelancer_portfolio_src($foto['imagem'])); ?>"
                       alt="<?php echo htmlspecialchars($foto['legenda'] !== null && $foto['legenda'] !== '' ? $foto['legenda'] : 'Foto do trabalho de ' . $freelancer['nome']); ?>"
                       <?php echo $indice === 0 ? 'loading="eager"' : 'loading="lazy"'; ?>>
                  <?php if ($foto['legenda'] !== null && $foto['legenda'] !== ''): ?>
                    <figcaption class="carrossel-legenda"><?php echo htmlspecialchars($foto['legenda']); ?></figcaption>
                  <?php endif; ?>
                </figure>
              <?php endforeach; ?>
            </div>

            <?php if ($totalCarrossel > 1): ?>
              <button type="button" class="carrossel-seta anterior" id="carrossel-anterior" aria-label="Foto anterior">&#10094;</button>
              <button type="button" class="carrossel-seta proximo" id="carrossel-proximo" aria-label="Próxima foto">&#10095;</button>
            <?php endif; ?>
          </div>

          <?php if ($totalCarrossel > 1): ?>
            <div class="carrossel-rodape">
              <ul class="carrossel-dots" id="carrossel-dots">
                <?php foreach ($fotosCarrossel as $indice => $foto): ?>
                  <li>
                    <button type="button" class="carrossel-dot" data-indice="<?php echo $indice; ?>"
                            aria-label="Ir para a foto <?php echo $indice + 1; ?>"
                            <?php echo $indice === 0 ? 'aria-current="true"' : ''; ?>></button>
                  </li>
                <?php endforeach; ?>
              </ul>
              <span class="carrossel-contador" id="carrossel-contador" aria-live="polite">1 / <?php echo $totalCarrossel; ?></span>
            </div>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="carrossel-vazio">Este artista ainda não adicionou fotos do trabalho.</div>
      <?php endif; ?>

      <?php if ($ehDono): ?>
        <div class="carrossel-gestao">
          <a href="CarrosselFreelancer.php" class="btn-contato ghost">+ Gerenciar fotos do trabalho</a>
        </div>
      <?php endif; ?>
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

      <!-- Abas -->
      <div class="abas-avaliacoes" role="tablist">
        <button class="aba-btn ativa" role="tab" aria-selected="true" aria-controls="aba-avaliacoes" id="tab-avaliacoes" onclick="mostrarAba('avaliacoes')">Avaliações</button>
        <?php if (isset($_SESSION['id_usuario']) && !$ehDono && !$jaAvaliou): ?>
        <button class="aba-btn" role="tab" aria-selected="false" aria-controls="aba-escrever" id="tab-escrever" onclick="mostrarAba('escrever')">Escrever Avaliação</button>
        <?php endif; ?>
      </div>

      <!-- Conteúdo da aba Avaliações -->
      <div class="aba-conteudo ativa" id="aba-avaliacoes" role="tabpanel" aria-labelledby="tab-avaliacoes">
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
      </div>

      <!-- Conteúdo da aba Escrever Avaliação -->
      <?php if (isset($_SESSION['id_usuario']) && !$ehDono && !$jaAvaliou): ?>
      <div class="aba-conteudo" id="aba-escrever" role="tabpanel" aria-labelledby="tab-escrever">
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
      <div class="aba-conteudo" id="aba-escrever" role="tabpanel" aria-labelledby="tab-escrever">
        <p style="margin-top:2rem;color:var(--paper-dim);">Você já avaliou este artista.</p>
      </div>
      <?php elseif (!isset($_SESSION['id_usuario'])): ?>
      <div class="aba-conteudo" id="aba-escrever" role="tabpanel" aria-labelledby="tab-escrever">
        <p style="margin-top:2rem;color:var(--paper-dim);"><a href="Login.php" style="color:var(--accent);">Faça login</a> para avaliar este artista.</p>
      </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<script>
function mostrarAba(abaId) {
  // Esconde todas as abas
  document.querySelectorAll('.aba-conteudo').forEach(function(el) {
    el.classList.remove('ativa');
  });
  document.querySelectorAll('.aba-btn').forEach(function(btn) {
    btn.classList.remove('ativa');
    btn.setAttribute('aria-selected', 'false');
  });
  
  // Mostra a aba selecionada
  document.getElementById('aba-' + abaId).classList.add('ativa');
  document.getElementById('tab-' + abaId).classList.add('ativa');
  document.getElementById('tab-' + abaId).setAttribute('aria-selected', 'true');
}

/* ============================================================
 * Carrossel de fotos do trabalho
 * Setas, dots, contador, autoplay (pausa no hover/foco),
 * teclado e swipe.
 * ============================================================ */
(function () {
  var raiz = document.getElementById('carrossel');
  if (!raiz) return;

  var trilha = document.getElementById('carrossel-trilha');
  if (!trilha) return;

  var slides = trilha.querySelectorAll('.carrossel-slide');
  var dots = raiz.querySelectorAll('.carrossel-dot');
  var btnAnterior = document.getElementById('carrossel-anterior');
  var btnProximo = document.getElementById('carrossel-proximo');
  var contador = document.getElementById('carrossel-contador');
  var total = slides.length;

  if (total === 0) return;

  var atual = 0;
  var timer = null;
  var PAUSA_MS = 5000;

  function irPara(indice) {
    if (indice < 0) indice = total - 1;
    if (indice >= total) indice = 0;

    atual = indice;
    trilha.style.transform = 'translateX(' + (-atual * 100) + '%)';

    for (var i = 0; i < slides.length; i++) {
      if (i === atual) slides[i].removeAttribute('aria-hidden');
      else slides[i].setAttribute('aria-hidden', 'true');
    }

    for (var d = 0; d < dots.length; d++) {
      if (d === atual) dots[d].setAttribute('aria-current', 'true');
      else dots[d].removeAttribute('aria-current');
    }

    if (contador) contador.textContent = (atual + 1) + ' / ' + total;
  }

  function proximo() { irPara(atual + 1); }
  function anterior() { irPara(atual - 1); }

  function pararAuto() {
    if (timer !== null) {
      clearInterval(timer);
      timer = null;
    }
  }

  function iniciarAuto() {
    pararAuto();
    if (total < 2) return;

    // Respeita quem prefere menos movimento.
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    timer = setInterval(proximo, PAUSA_MS);
  }

  if (btnAnterior) {
    btnAnterior.addEventListener('click', function () {
      anterior();
      iniciarAuto();
    });
  }

  if (btnProximo) {
    btnProximo.addEventListener('click', function () {
      proximo();
      iniciarAuto();
    });
  }

  for (var k = 0; k < dots.length; k++) {
    dots[k].addEventListener('click', (function (indice) {
      return function () {
        irPara(indice);
        iniciarAuto();
      };
    })(k));
  }

  // Pausa o autoplay enquanto o mouse está sobre o carrossel,
  // ou enquanto o foco está dentro dele.
  raiz.addEventListener('mouseenter', pararAuto);
  raiz.addEventListener('mouseleave', iniciarAuto);
  raiz.addEventListener('focusin', pararAuto);
  raiz.addEventListener('focusout', function (evento) {
    if (!raiz.contains(evento.relatedTarget)) iniciarAuto();
  });

  // Teclado: setas navegam quando o carrossel tem foco.
  raiz.addEventListener('keydown', function (evento) {
    var tecla = evento.key !== undefined ? evento.key : evento.keyCode;

    if (tecla === 'ArrowLeft' || tecla === 37) {
      evento.preventDefault();
      anterior();
      iniciarAuto();
    } else if (tecla === 'ArrowRight' || tecla === 39) {
      evento.preventDefault();
      proximo();
      iniciarAuto();
    } else if (tecla === 'Home' || tecla === 36) {
      evento.preventDefault();
      irPara(0);
      iniciarAuto();
    } else if (tecla === 'End' || tecla === 35) {
      evento.preventDefault();
      irPara(total - 1);
      iniciarAuto();
    }
  });

  // Swipe em telas de toque.
  var xInicial = null;

  trilha.addEventListener('touchstart', function (evento) {
    if (evento.touches.length === 1) {
      xInicial = evento.touches[0].clientX;
      pararAuto();
    }
  }, { passive: true });

  trilha.addEventListener('touchend', function (evento) {
    if (xInicial === null) return;

    var xFinal = evento.changedTouches[0].clientX;
    var deslocamento = xFinal - xInicial;
    xInicial = null;

    if (Math.abs(deslocamento) < 50) {
      iniciarAuto();
      return;
    }

    if (deslocamento < 0) proximo();
    else anterior();

    iniciarAuto();
  }, { passive: true });

  // Para o autoplay com a aba oculta.
  document.addEventListener('visibilitychange', function () {
    if (document.hidden) pararAuto();
    else iniciarAuto();
  });

  irPara(0);
  iniciarAuto();
})();
</script>

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>
</body>
</html>