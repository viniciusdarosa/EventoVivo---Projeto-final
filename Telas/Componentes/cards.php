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
    static $estilos_inseridos = false;

    if (!$estilos_inseridos) {
        $estilos_inseridos = true;
        ?>
        <style>
          .event-card {
            position: relative;
            display: flex;
            flex-direction: column;
            min-width: 0;
            background: var(--steel);
            border: 1px solid var(--rule);
            box-shadow: var(--shadow-hard);
            overflow: hidden;
            transition: transform var(--ease), border-color var(--ease), box-shadow var(--ease);
          }

          .event-card:hover {
            transform: translateY(-2px);
            border-color: var(--accent);
            box-shadow: 8px 8px 0 rgba(0,0,0,.55);
          }

          .event-card-imagem-wrap {
            width: 100%;
            aspect-ratio: 16 / 9;
            overflow: hidden;
            background: var(--ink-2);
            border-bottom: 3px solid var(--accent);
          }

          .event-card-imagem,
          .event-card-placeholder {
            width: 100%;
            height: 100%;
            display: block;
          }

          .event-card-imagem {
            object-fit: cover;
            object-position: center;
          }

          .event-card-placeholder svg {
            width: 100%;
            height: 100%;
            display: block;
          }

          .event-card-corpo {
            display: flex;
            flex-direction: column;
            flex: 1;
            padding: var(--sp-3);
          }

          .event-card .tag {
            color: var(--accent);
            font-family: var(--font-mark);
            font-size: .9rem;
            line-height: 1.2;
            margin: 0 0 .45rem;
          }

          .event-card h3 {
            font-family: var(--font-display);
            font-size: 1.35rem;
            line-height: 1.05;
            text-transform: uppercase;
            letter-spacing: .01em;
            margin: 0 0 .8rem;
          }

          .event-card .desc {
            color: var(--paper);
            font-size: .82rem;
            line-height: 1.45;
            margin: 0;
          }

          @media (max-width: 600px) {
            .event-card-corpo {
              padding: var(--sp-2);
            }
          }
        </style>
        <?php
    }

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
          <img
            class="event-card-imagem"
            src="<?php echo $imagem; ?>"
            alt="Capa do evento <?php echo $titulo; ?>"
            loading="lazy">
        <?php else: ?>
          <div class="event-card-placeholder">
            <svg viewBox="0 0 400 225" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Sem imagem">
              <rect width="400" height="225" fill="#1e1a12"/>
              <text x="200" y="120" font-family="Arial, sans-serif" font-weight="700"
                    font-size="18" fill="#a89d80" text-anchor="middle">Sem imagem</text>
            </svg>
          </div>
        <?php endif; ?>
      </div>

      <div class="event-card-corpo">
        <?php if ($data !== '' || $local !== ''): ?>
          <p class="tag">
            <?php echo $data; ?><?php echo ($data !== '' && $local !== '') ? ' · ' : ''; ?><?php echo $local; ?>
          </p>
        <?php endif; ?>

        <h3><?php echo $titulo; ?></h3>

        <?php if ($desc !== ''): ?>
          <p class="desc"><?php echo $desc; ?></p>
        <?php endif; ?>
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