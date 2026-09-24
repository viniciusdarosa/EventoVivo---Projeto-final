<?php
/* ==========================================================
 * COMPONENTE: cards.php
 * Componentes reutilizáveis responsáveis por gerar o HTML dos cards de eventos e artistas.
 * ========================================================== */

/**
 * cards.php
 * Componentes reutilizáveis de card. Cada função recebe um array
 * associativo com os dados e imprime o HTML do card.
 * Campos esperados: data, local, titulo, desc, imagem (opcional)
 */
function render_event_card($evento) {
    $data   = isset($evento['data'])   ? htmlspecialchars($evento['data'])   : '';
    $local  = isset($evento['local'])  ? htmlspecialchars($evento['local'])  : '';
    $titulo = isset($evento['titulo']) ? htmlspecialchars($evento['titulo']) : '';
    $desc   = isset($evento['desc'])   ? htmlspecialchars($evento['desc'])   : '';
    $imagem = isset($evento['imagem']) ? htmlspecialchars($evento['imagem']) : '';
    $temImagem = $imagem !== '' && strpos($imagem, 'data:image') !== 0;
    ?>
    <article class="card event-card">
      <div class="event-card-imagem-wrap">
        <?php if ($temImagem): ?>
          <img class="event-card-imagem" src="<?php echo $imagem; ?>" alt="Capa do evento <?php echo htmlspecialchars($evento['titulo']); ?>">
        <?php else: ?>
          <div class="event-card-placeholder">
            <svg viewBox="0 0 400 225" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect width="400" height="225" fill="#1e1a12"/>
              <text x="200" y="120" font-family="Arial, sans-serif" font-weight="700" font-size="18" fill="#a89d80" text-anchor="middle">Sem imagem</text>
            </svg>
          </div>
        <?php endif; ?>
      </div>
      <div class="event-card-corpo">
        <p class="tag"><?php echo $data; ?> · <?php echo $local; ?></p>
        <h3><?php echo $titulo; ?></h3>
        <p class="desc"><?php echo $desc; ?></p>
      </div>
    </article>
    <?php
}

/**
 * Card de artista.
 * Campos esperados: foto, nome, tipo (trabalho artístico), local, categorias (array)
 */
function render_artist_card($artista) {
    $foto       = isset($artista['foto'])  ? htmlspecialchars($artista['foto'])  : '';
    $nome       = isset($artista['nome'])  ? htmlspecialchars($artista['nome'])  : '';
    $tipo       = isset($artista['tipo'])  ? htmlspecialchars($artista['tipo'])  : '';
    $local      = isset($artista['local']) ? htmlspecialchars($artista['local']) : '';
    $categorias = isset($artista['categorias']) ? $artista['categorias'] : array();
    ?>
    <article class="card artist-card">
      <div class="artist-photo-wrap">
        <img class="artist-photo" src="<?php echo $foto; ?>" alt="Foto de <?php echo $nome; ?>">
      </div>
      <div class="artist-body">
        <h3 class="artist-nome"><?php echo $nome; ?></h3>
        <p class="artist-tipo"><?php echo $tipo; ?></p>
        <p class="artist-local"><?php echo $local; ?></p>
        <?php if (!empty($categorias)): ?>
        <ul class="categorias">
          <?php foreach ($categorias as $categoria): ?>
          <li class="categoria"><?php echo htmlspecialchars($categoria); ?></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
    </article>
    <?php
}