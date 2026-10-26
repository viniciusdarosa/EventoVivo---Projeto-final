<?php
/* ==========================================================
 * COMPONENTE: MeusFavoritos.php
 * Lista os favoritos do usuário logado: eventos salvos e perfis
 * de artista salvos, com estados vazios e botão de remoção.
 * ========================================================== */

/**
 * MeusFavoritos.php — favoritos do usuário logado
 */
require_once dirname(__FILE__) . '/../config/bootstrap.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_eventos.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_freelancers.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_favoritos.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_contato.php';
require_once dirname(__FILE__) . '/Componentes/EventoCard.php';

exigir_login('Login.php');

$usuarioId = usuario_id();

$eventosFavoritos = favoritos_listar_eventos($conexao, $usuarioId);
$artistasFavoritos = favoritos_listar_artistas($conexao, $usuarioId);

$pageTitle = 'EventoVivo - Meus Favoritos';
$pageDescription = 'Eventos e artistas que você salvou no EventoVivo.';
$extraCss = array();

require dirname(__FILE__) . '/Componentes/header.php';
$destinoAtual = 'MeusFavoritos.php';
?>

<main class="eventos-page">
  <section class="page-header">
    <div class="wrap">
      <p class="eyebrow">SUA LISTA</p>
      <h1>MEUS FAVORITOS</h1>
      <p class="hero-sub">Eventos e artistas que você salvou para ver depois.</p>
    </div>
  </section>

  <section class="results-section">
    <div class="wrap">

      <!-- EVENTOS FAVORITADOS -->
      <div class="results-header">
        <h2>Eventos salvos <span class="text-accent">(<?php echo count($eventosFavoritos); ?>)</span></h2>
      </div>

      <?php if (empty($eventosFavoritos)): ?>
        <div class="empty-state">
          <p>Você ainda não salvou nenhum evento. Use o botão ☆ Salvar nos cards dos eventos.</p>
          <a class="btn btn-primary" href="Eventos.php">Ver todos os eventos</a>
        </div>
      <?php else: ?>
        <div class="eventos-grid">
          <?php foreach ($eventosFavoritos as $evento): ?>
            <?php render_evento_card($evento); ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- ARTISTAS FAVORITADOS -->
      <div class="results-header destacado">
        <h2>Artistas salvos <span class="text-accent">(<?php echo count($artistasFavoritos); ?>)</span></h2>
      </div>

      <?php if (empty($artistasFavoritos)): ?>
        <div class="empty-state">
          <p>Você ainda não salvou nenhum artista. Abra um perfil de artista e use o botão ☆ Salvar.</p>
          <a class="btn btn-primary" href="Artistas.php">Ver todos os artistas</a>
        </div>
      <?php else: ?>
        <div class="artistas-grid">
          <?php foreach ($artistasFavoritos as $artista): ?>
            <article class="artist-card-public">
              <div class="artist-photo-wrap">
                <?php
                  $imagemArtista = freelancer_imagem_publica_src($artista['foto_perfil']);
                ?>
                <?php if (strpos($imagemArtista, 'data:image') === 0): ?>
                  <div class="artist-photo-placeholder">
                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <circle cx="32" cy="24" r="12" stroke="currentColor" stroke-width="2"/>
                      <path d="M8 56c0-13.255 10.745-24 24-24s24 10.745 24 24" stroke="currentColor" stroke-width="2"/>
                    </svg>
                  </div>
                <?php else: ?>
                  <img class="artist-photo" src="<?php echo htmlspecialchars($imagemArtista); ?>"
                       alt="Foto de <?php echo htmlspecialchars($artista['nome']); ?>">
                <?php endif; ?>
              </div>

              <div class="artist-body">
                <h3 class="artist-nome"><?php echo htmlspecialchars($artista['nome']); ?></h3>

                <?php if (!empty($artista['categoria_nome'])): ?>
                <p class="artist-tipo"><?php echo htmlspecialchars($artista['categoria_nome']); ?></p>
                <?php endif; ?>

                <?php if (!empty($artista['profissao'])): ?>
                <p class="artist-titulo"><?php echo htmlspecialchars($artista['profissao']); ?></p>
                <?php endif; ?>

                <p class="artist-local">
                  <?php echo htmlspecialchars($artista['cidade']); ?>/<?php echo htmlspecialchars($artista['estado']); ?>
                </p>

                <?php
                  $media = isset($artista['media_avaliacao']) ? (float) $artista['media_avaliacao'] : 0;
                  $total = isset($artista['total_avaliacoes']) ? (int) $artista['total_avaliacoes'] : 0;
                  if ($total > 0):
                ?>
                <div class="artist-avaliacao">
                  <?php echo render_estrelas_avaliacao($media); ?>
                  <span class="avaliacao-count">(<?php echo $total; ?> avaliaç<?php echo $total === 1 ? 'ão' : 'ões'; ?>)</span>
                </div>
                <?php endif; ?>

                <?php if (!empty($artista['biografia'])): ?>
                <p class="artist-bio"><?php echo htmlspecialchars(substr($artista['biografia'], 0, 120)); ?>...</p>
                <?php endif; ?>

                <div class="artist-contato">
                  <?php if (!empty($artista['valor_hora']) && $artista['valor_hora'] > 0): ?>
                    <span class="artist-valor">R$ <?php echo number_format($artista['valor_hora'], 2, ',', '.'); ?>/h</span>
                  <?php endif; ?>
                  <?php render_favorito_form('freelancer', (int) $artista['id_freelancer'], $destinoAtual, true); ?>
                  <a href="PerfilFreelancer.php?id=<?php echo (int) $artista['id_freelancer']; ?>" class="contact-link">Ver Perfil</a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </section>
</main>

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>
