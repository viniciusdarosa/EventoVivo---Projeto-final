<?php
/**
 * cards.php
 * Componentes reutilizáveis de card. Cada função recebe um array
 * associativo com os dados e imprime o HTML do card.
 *
 * Compatível com PHP 5.2.0: sem type hint de retorno (7.0+), sem
 * operador ?? (7.0+) e sem depender de <?= (short_open_tag).
 *
 * Uso:
 *   foreach ($eventos as $evento)   { render_event_card($evento); }
 *   foreach ($artistas as $artista) { render_artist_card($artista); }
 */

/**
 * Card de evento.
 * Campos esperados: data, local, titulo, desc
 */
function render_event_card($evento) {
    $data   = isset($evento['data'])   ? htmlspecialchars($evento['data'])   : '';
    $local  = isset($evento['local'])  ? htmlspecialchars($evento['local'])  : '';
    $titulo = isset($evento['titulo']) ? htmlspecialchars($evento['titulo']) : '';
    $desc   = isset($evento['desc'])   ? htmlspecialchars($evento['desc'])   : '';
    ?>
    <article class="card event-card">
      <p class="tag"><?php echo $data; ?> · <?php echo $local; ?></p>
      <h3><?php echo $titulo; ?></h3>
      <p class="desc"><?php echo $desc; ?></p>
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