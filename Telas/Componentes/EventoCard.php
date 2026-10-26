<?php
/* ==========================================================
 * COMPONENTE: EventoCard.php
 * Card reutilizável exclusivo para a listagem pública de eventos.
 *
 * A área da imagem pertence ao próprio componente: ela usa uma
 * moldura 16:9 e object-fit: cover, independentemente do tamanho
 * ou proporção do arquivo original enviado pelo usuário.
 * ========================================================== */

require_once dirname(__FILE__) . '/funcoes_contato.php';
require_once dirname(__FILE__) . '/funcoes_favoritos.php';

if (!function_exists('render_evento_card')) {

    function render_evento_card($evento)
    {
        global $conexao;

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
                <?php
                // Favorito: destino = página atual com a querystring
                // original (o formulário volta exatamente para onde o
                // usuário estava, dentro do whitelist interno).
                $destinoAtual = basename(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'Eventos.php');
                if (!preg_match('/^[A-Za-z0-9_-]+\.php$/', $destinoAtual)) {
                    $destinoAtual = 'Eventos.php';
                }
                $consultaAtual = isset($_GET) && is_array($_GET) ? http_build_query($_GET) : '';
                $destinoCard = $destinoAtual;
                if ($consultaAtual !== '' && preg_match('/^[A-Za-z0-9_=&%.-]+$/', $consultaAtual)) {
                    $destinoCard .= '?' . $consultaAtual;
                }
                $salvoEvento = false;
                if (isset($_SESSION['id_usuario']) && $idEvento > 0 && $conexao instanceof mysqli) {
                    $salvoEvento = favorito_esta($conexao, (int) $_SESSION['id_usuario'], 'evento', $idEvento);
                }
                render_favorito_form('evento', $idEvento, $destinoCard, $salvoEvento);
                ?>
                <?php if (isset($_SESSION['id_usuario']) && $_SESSION['id_usuario'] == $usuarioEvento): ?>
                  <a
                    class="btn-icone"
                    href="EditarEvento.php?id_evento=<?php echo $idEvento; ?>">
                    Editar
                  </a>

                  <form
                    method="post"
                    action="ExcluirEvento.php"
                    onsubmit="return confirm('Tem certeza que deseja arquivar este evento? Ele deixará de aparecer nas listagens públicas e poderá ser restaurado depois.');">
                    <?php echo csrf_campo(); ?>
                    <input type="hidden" name="id_evento" value="<?php echo $idEvento; ?>">
                    <button type="submit" class="btn-icone btn-icone-excluir">
                      Arquivar
                    </button>
                  </form>
                <?php else: ?>
                  <?php
                  // Contato com o organizador: localizado pelo usuário DONO
                  // do evento (eventos.usuario_id), nunca por id de freelancer.
                  // Visitante recebe pedido de login; autenticado vê os canais
                  // efetivamente cadastrados (WhatsApp/e-mail).
                  render_contato_organizador(
                      $usuarioEvento,
                      $organizador,
                      isset($evento['organizador_email']) ? $evento['organizador_email'] : '',
                      isset($evento['organizador_telefone']) ? $evento['organizador_telefone'] : ''
                  );
                  ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </article>
        <?php
    }
}
