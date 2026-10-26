<?php
/* ==========================================================
 * COMPONENTE: PerfilFreelancer.php
 * Apresenta o perfil público completo de um freelancer, incluindo dados profissionais, carrossel de trabalhos e avaliações.
 * ========================================================== */

/**
 * PerfilFreelancer.php — Perfil público de um freelancer/artista
 */
require_once dirname(__FILE__) . '/../config/bootstrap.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_freelancers.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_eventos.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_contato.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_favoritos.php';

// ID do freelancer vem da URL
$idFreelancer = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int) $_GET['id'] : 0;

if ($idFreelancer <= 0) {
    flash_definir('Artista não encontrado.', 'erro');
    redirecionar('Artistas.php');
}

// Busca dados completos do freelancer
$freelancer = buscar_freelancer_completo($conexao, $idFreelancer);

if (!$freelancer) {
    flash_definir('Artista não encontrado.', 'erro');
    redirecionar('Artistas.php');
}

// Busca fotos do carrossel do trabalho
$fotosCarrossel = buscar_carrossel_freelancer($conexao, $idFreelancer);
$totalCarrossel = count($fotosCarrossel);

// Busca avaliações
$avaliacoes = buscar_avaliacoes_freelancer($conexao, $idFreelancer);
$mediaAvaliacoes = calcular_media_avaliacoes($conexao, $idFreelancer);

// Verifica se o usuário logado é o dono do perfil
$ehDono = isset($_SESSION['id_usuario']) && $_SESSION['id_usuario'] == $freelancer['usuario_id'];

// Estado do favorito deste perfil (botão Salvar/Salvo do cabeçalho)
$salvoFavorito = false;
if (isset($_SESSION['id_usuario'])) {
    $salvoFavorito = favorito_esta($conexao, (int) $_SESSION['id_usuario'], 'freelancer', $idFreelancer);
}

// Verifica se já avaliou
$jaAvaliou = false;
if (isset($_SESSION['id_usuario']) && !$ehDono) {
    $stmt = $conexao->prepare("SELECT id FROM avaliacoes WHERE avaliador = ? AND freelancer_id = ? LIMIT 1");
    if ($stmt !== false) {
        $stmt->bind_param('ii', $_SESSION['id_usuario'], $idFreelancer);
        $stmt->execute();
        $stmt->store_result();
        $jaAvaliou = $stmt->num_rows > 0;
        $stmt->close();
    }
}

$pageTitle = $freelancer['nome'] . ' — EventoVivo';
$pageDescription = substr($freelancer['descricao'], 0, 160);
$extraCss = array('../Css/perfil.css');

require dirname(__FILE__) . '/Componentes/header.php';
?>

<main class="perfil-page">
  <!-- Hero com a foto de perfil -->
  <section class="perfil-hero">
    <div class="wrap">
      <div class="perfil-header">
        <?php
        $srcFoto = !empty($freelancer['foto_perfil'])
            ? freelancer_foto_src($freelancer['foto_perfil'])
            : '';
        if ($srcFoto !== ''):
        ?>
          <img class="perfil-foto" src="<?php echo htmlspecialchars($srcFoto); ?>" alt="Foto de perfil de <?php echo htmlspecialchars($freelancer['nome']); ?>">
        <?php endif; ?>

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
            <?php $urlWhatsPerfil = whatsapp_url(isset($freelancer['telefone']) ? $freelancer['telefone'] : ''); ?>
            <?php if ($urlWhatsPerfil !== ''): ?>
              <a href="<?php echo htmlspecialchars($urlWhatsPerfil); ?>" target="_blank" rel="noopener noreferrer" class="btn-contato">📞 WhatsApp</a>
            <?php endif; ?>
            <?php if (!empty($freelancer['rede_social'])): ?>
              <a href="https://instagram.com/<?php echo ltrim($freelancer['rede_social'], '@'); ?>" target="_blank" class="btn-contato ghost">📸 Instagram</a>
            <?php endif; ?>
            <?php
            render_favorito_form(
                'freelancer',
                $idFreelancer,
                'PerfilFreelancer.php?id=' . $idFreelancer,
                $salvoFavorito
            );
            ?>
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
        <h3 class="perfil-subtitulo">Experiência</h3>
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
  <section class="perfil-section alt">
    <div class="wrap">
      <h2 class="section-title">Trabalhos</h2>

      <?php if ($totalCarrossel > 0): ?>
        <div class="carrossel" id="carrossel" role="region" aria-roledescription="carrossel" aria-label="Fotos do trabalho de <?php echo htmlspecialchars($freelancer['nome']); ?>" tabindex="0">
          <div class="carrossel-viewport">
            <div class="carrossel-trilha" id="carrossel-trilha">
              <?php foreach ($fotosCarrossel as $indice => $foto): ?>
                <figure class="carrossel-slide<?php echo $indice === 0 ? ' ativo' : ''; ?>" role="group" aria-roledescription="slide"
                        aria-label="<?php echo $indice + 1; ?> de <?php echo $totalCarrossel; ?>"
                        <?php if ($indice > 0): ?>aria-hidden="true"<?php endif; ?>>
                  <img src="<?php echo htmlspecialchars(freelancer_foto_trabalho_src($foto['imagem'])); ?>"
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
        <div class="carrossel-vazio">Nenhum trabalho publicado por enquanto.</div>
      <?php endif; ?>

      <?php if ($ehDono): ?>
        <div class="carrossel-gestao">
          <a href="CarrosselFreelancer.php" class="btn-contato ghost">+ Gerenciar fotos do trabalho</a>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Avaliações -->
  <section class="perfil-section">
    <div class="wrap">
      <div class="perfil-avaliacoes-topo">
        <h2 class="section-title">Avaliações</h2>
        <div class="estrelas-avaliacao">
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
        <h3 class="perfil-subtitulo avaliar">Deixe sua avaliação</h3>
        <form class="avaliar-form" action="AvaliarFreelancer.php" method="post" data-loading="true">
          <?php echo csrf_campo(); ?>
          <input type="hidden" name="freelancer_id" value="<?php echo $idFreelancer; ?>">
          <input type="hidden" name="redirect" value="PerfilFreelancer.php?id=<?php echo $idFreelancer; ?>">

          <div class="campo">
              <label>Sua nota</label>
              <div class="estrelas-input esquerda">
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
        <p class="perfil-nota">Você já avaliou este artista.</p>
      </div>
      <?php elseif (!isset($_SESSION['id_usuario'])): ?>
      <div class="aba-conteudo" id="aba-escrever" role="tabpanel" aria-labelledby="tab-escrever">
        <p class="perfil-nota"><a href="Login.php" class="text-accent">Faça login</a> para avaliar este artista.</p>
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

    for (var i = 0; i < slides.length; i++) {
      if (i === atual) {
        slides[i].className = 'carrossel-slide ativo';
        slides[i].removeAttribute('aria-hidden');
      } else {
        slides[i].className = 'carrossel-slide';
        slides[i].setAttribute('aria-hidden', 'true');
      }
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
