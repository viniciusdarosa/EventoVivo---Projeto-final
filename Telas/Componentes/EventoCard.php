<?php
/* ==========================================================
 * COMPONENTE: EventoCard.php
 * Card reutilizável exclusivo para a listagem pública de eventos.
 *
 * A área da imagem pertence ao próprio componente: ela usa uma
 * moldura 16:9 e object-fit: cover, independentemente do tamanho
 * ou proporção do arquivo original enviado pelo usuário.
 * ========================================================== */

if (!function_exists('render_evento_card')) {

    function render_evento_card($evento)
    {
        static $estilos_inseridos = false;

        if (!$estilos_inseridos) {
            $estilos_inseridos = true;
            ?>
            <style>
              .evento-card-novo {
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

              .evento-card-novo:hover {
                transform: translateY(-2px);
                border-color: var(--accent);
                box-shadow: 8px 8px 0 rgba(0,0,0,.55);
              }

              .evento-card-novo-imagem {
                width: 100%;
                aspect-ratio: 16 / 9;
                overflow: hidden;
                background: var(--ink-2);
                border-bottom: 3px solid var(--accent);
              }

              .evento-card-novo-imagem img {
                width: 100%;
                height: 100%;
                display: block;
                object-fit: cover;
                object-position: center;
              }

              .evento-card-novo-corpo {
                display: flex;
                flex-direction: column;
                flex: 1;
                padding: var(--sp-3);
              }

              .evento-card-novo-categoria {
                color: var(--accent);
                font-family: var(--font-mark);
                font-size: .9rem;
                line-height: 1.2;
                margin-bottom: .45rem;
              }

              .evento-card-novo-titulo {
                font-family: var(--font-display);
                font-size: 1.35rem;
                line-height: 1.05;
                text-transform: uppercase;
                letter-spacing: .01em;
                margin-bottom: .8rem;
              }

              .evento-card-novo-meta {
                display: flex;
                flex-direction: column;
                gap: .25rem;
                color: var(--paper);
                font-size: .82rem;
                line-height: 1.45;
              }

              .evento-card-novo-meta .secundario {
                color: var(--paper-dim);
              }

              .evento-card-novo-rodape {
                display: flex;
                align-items: flex-end;
                justify-content: space-between;
                gap: .75rem;
                flex-wrap: wrap;
                margin-top: auto;
                padding-top: var(--sp-2);
              }

              .evento-card-novo-valor {
                font-weight: 700;
                color: var(--white);
                font-size: .95rem;
              }

              .evento-card-novo-acoes {
                display: flex;
                gap: .4rem;
                flex-wrap: wrap;
              }

              .evento-card-novo-acoes .btn-icone {
                font-size: .7rem;
                padding: .25rem .5rem;
              }

              @media (max-width: 600px) {
                .evento-card-novo-corpo {
                  padding: var(--sp-2);
                }
              }
            </style>
            <?php
        }

        $imagem = evento_imagem_src(
            isset($evento['imagem_capa']) ? $evento['imagem_capa'] : ''
        );

        $titulo = isset($evento['titulo']) ? $evento['titulo'] : '';
        $categoria = isset($evento['categoria_nome']) ? $evento['categoria_nome'] : '';
        $cidade = isset($evento['cidade']) ? $evento['cidade'] : '';
        $estado = isset($evento['estado']) ? $evento['estado'] : '';
        $organizador = isset($evento['organizador_nome']) ? $evento['organizador_nome'] : '';
        $vagas = isset($evento['vagas']) ? (int) $evento['vagas'] : 0;
        $valor = isset($evento['valor']) ? $evento['valor'] : null;
        $idEvento = isset($evento['id_evento']) ? (int) $evento['id_evento'] : 0;
        $usuarioEvento = isset($evento['usuario_id']) ? $evento['usuario_id'] : null;
        ?>
        <article class="evento-card-novo">
          <div class="evento-card-novo-imagem">
            <img
              src="<?php echo htmlspecialchars($imagem); ?>"
              alt="Capa do evento <?php echo htmlspecialchars($titulo); ?>"
              loading="lazy">
          </div>

          <div class="evento-card-novo-corpo">
            <?php if ($categoria !== ''): ?>
              <p class="evento-card-novo-categoria">
                <?php echo htmlspecialchars($categoria); ?>
              </p>
            <?php endif; ?>

            <h3 class="evento-card-novo-titulo">
              <?php echo htmlspecialchars($titulo); ?>
            </h3>

            <div class="evento-card-novo-meta">
              <span>
                <?php echo htmlspecialchars(formatar_data_evento($evento['data_inicio_evento'])); ?>
                às <?php echo htmlspecialchars(substr($evento['hora_inicio'], 0, 5)); ?>
              </span>

              <span>
                <?php echo htmlspecialchars($cidade); ?>/<?php echo htmlspecialchars($estado); ?>
              </span>

              <?php if ($organizador !== ''): ?>
                <span class="secundario">
                  Por <?php echo htmlspecialchars($organizador); ?>
                </span>
              <?php endif; ?>

              <span class="secundario">
                <?php echo $vagas; ?> vaga(s)
              </span>
            </div>

            <div class="evento-card-novo-rodape">
              <span class="evento-card-novo-valor">
                <?php echo formatar_valor_evento($valor); ?>
              </span>

              <div class="evento-card-novo-acoes">
                <?php if (isset($_SESSION['id_usuario']) && $_SESSION['id_usuario'] == $usuarioEvento): ?>
                  <a
                    class="btn-icone"
                    href="EditarEvento.php?id_evento=<?php echo $idEvento; ?>">
                    Editar
                  </a>

                  <form
                    method="post"
                    action="ExcluirEvento.php"
                    onsubmit="return confirm('Tem certeza que deseja excluir este evento?');"
                    style="display:inline;">
                    <input type="hidden" name="id_evento" value="<?php echo $idEvento; ?>">
                    <button type="submit" class="btn-icone btn-icone-excluir">
                      Excluir
                    </button>
                  </form>
                <?php else: ?>
                  <a
                    class="btn-icone"
                    href="#contato"
                    onclick="alert('Faça login para entrar em contato com o organizador.')">
                    Contatar
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </article>
        <?php
    }
}
