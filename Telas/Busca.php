<?php
/**
 * Busca.php — Busca geral unificada (eventos + artistas/freelancers)
 * Compatível com PHP 5.3.9
 */
session_start();
require_once dirname(__FILE__) . '/../config/conexao.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_eventos.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_freelancers.php';

$termo = isset($_GET['q']) ? trim($_GET['q']) : '';
$termoSeguro = htmlspecialchars($termo);

$eventos = array();
$artistas = array();

if ($termo !== '') {
    // ---- Busca EVENTOS ----
    $sqlEventos = "SELECT e.*, c.nome AS categoria_nome, u.nome AS organizador_nome
                   FROM eventos e
                   LEFT JOIN categorias_eventos c ON c.id_categoria = e.categoria_id
                   LEFT JOIN usuario u ON u.id_usuario = e.usuario_id
                   WHERE e.titulo LIKE ? OR e.descricao LIKE ? OR e.cidade LIKE ? OR e.endereco LIKE ?
                   ORDER BY e.data_inicio_evento DESC
                   LIMIT 20";

    $param = '%' . $termo . '%';
    $stmt = $conexao->prepare($sqlEventos);
    $stmt->bind_param('ssss', $param, $param, $param, $param);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $eventos[] = $row;
    }
    $stmt->close();

    // ---- Busca ARTISTAS/FREELANCERS ----
    $sqlArtistas = "SELECT f.*, u.nome, u.foto_perfil, u.biografia, u.cidade, u.estado, u.email, u.telefone,
                           cs.nome AS categoria_nome
                    FROM freelancers f
                    LEFT JOIN usuario u ON u.id_usuario = f.usuario_id
                    LEFT JOIN categorias_servicos cs ON cs.id_categoria = f.categoria_id
                    WHERE u.nome LIKE ? OR f.profissao LIKE ? OR f.descricao LIKE ? OR f.experiencia LIKE ? OR u.cidade LIKE ?
                    ORDER BY u.nome ASC
                    LIMIT 20";

    $stmt = $conexao->prepare($sqlArtistas);
    $stmt->bind_param('sssss', $param, $param, $param, $param, $param);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $artistas[] = $row;
    }
    $stmt->close();
}
?>

<?php
$pageTitle = $termoSeguro ? 'Busca por "' . $termoSeguro . '"' : 'Busca Geral';
$pageDescription = $termoSeguro ? 'Resultados da busca por "' . $termoSeguro . '"' : 'Busca geral de eventos e artistas no EventoVivo';
$extraCss = array('../Css/style.css', '../Css/crud_eventos.css');

require dirname(__FILE__) . '/Componentes/header.php';
?>

<main class="busca-page">
  <section class="page-header">
    <div class="wrap">
      <p class="eyebrow">BUSCA GERAL</p>
      <h1><?php echo $termoSeguro ? 'Resultados para <span style="color:var(--accent);">"' . $termoSeguro . '"</span>' : 'Busca Geral'; ?></h1>
      <?php if ($termo !== ''): ?>
        <p class="hero-sub">
          Encontramos <strong><?php echo count($eventos); ?> evento(s)</strong> e <strong><?php echo count($artistas); ?> artista(s)</strong>.
        </p>
      <?php else: ?>
        <p class="hero-sub">Digite algo acima para buscar eventos, artistas, cidades ou categorias.</p>
      <?php endif; ?>
    </div>
  </section>

  <section class="busca-resultados">
    <div class="wrap">

      <!-- EVENTOS -->
      <section class="resultados-bloco">
        <div class="resultados-header">
          <h2>Eventos <span class="counter"><?php echo count($eventos); ?></span></h2>
          <?php if ($termo !== '' && count($eventos) >= 20): ?>
            <a href="Eventos.php?busca=<?php echo urlencode($termo); ?>" class="btn btn-ghost" style="font-size:.8rem;">Ver todos os eventos</a>
          <?php endif; ?>
        </div>

        <?php if ($termo === ''): ?>
          <div class="estado-vazio">
            <p>Digite um termo na busca acima para encontrar eventos.</p>
          </div>
        <?php elseif (empty($eventos)): ?>
          <div class="estado-vazio">
            <p>Nenhum evento encontrado para "<strong><?php echo $termoSeguro; ?></strong>".</p>
            <a href="Eventos.php" class="btn btn-primary">Ver todos os eventos</a>
          </div>
        <?php else: ?>
          <div class="eventos-grid">
            <?php foreach ($eventos as $evento): ?>
              <article class="evento-card">
                <img class="evento-card-imagem"
                     src="<?php echo htmlspecialchars(evento_imagem_src($evento['imagem_capa'])); ?>"
                     alt="Capa do evento <?php echo htmlspecialchars($evento['titulo']); ?>">

                <div class="evento-card-corpo">
                  <p class="evento-card-categoria"><?php echo htmlspecialchars($evento['categoria_nome']); ?></p>
                  <h3 class="evento-card-titulo"><?php echo htmlspecialchars($evento['titulo']); ?></h3>

                  <p class="evento-card-info">
                    <?php echo htmlspecialchars(formatar_data_evento($evento['data_inicio_evento'])); ?>
                    às <?php echo htmlspecialchars(substr($evento['hora_inicio'], 0, 5)); ?>
                    — <?php echo htmlspecialchars($evento['cidade']); ?>/<?php echo htmlspecialchars($evento['estado']); ?>
                  </p>

                  <?php if (!empty($evento['organizador_nome'])): ?>
                  <p class="evento-card-info">
                    Por <?php echo htmlspecialchars($evento['organizador_nome']); ?>
                  </p>
                  <?php endif; ?>

                  <p class="evento-card-info">
                    <?php echo (int) $evento['vagas']; ?> vaga(s)
                  </p>

                  <div class="evento-card-rodape">
                    <span class="evento-card-valor"><?php echo formatar_valor_evento($evento['valor']); ?></span>

                    <div class="evento-card-acoes">
                      <?php if (isset($_SESSION['id_usuario']) && $_SESSION['id_usuario'] == $evento['usuario_id']): ?>
                        <a class="btn-icone" href="EditarEvento.php?id_evento=<?php echo (int) $evento['id_evento']; ?>">Editar</a>
                        <form method="post" action="ExcluirEvento.php"
                              onsubmit="return confirm('Tem certeza que deseja excluir este evento?');"
                              style="display:inline;">
                          <input type="hidden" name="id_evento" value="<?php echo (int) $evento['id_evento']; ?>">
                          <button type="submit" class="btn-icone btn-icone-excluir">Excluir</button>
                        </form>
                      <?php else: ?>
                        <a class="btn-icone" href="PerfilFreelancer.php?id=<?php echo (int) $evento['id_evento']; ?>" style="pointer-events:none;opacity:.5;" title="Faça login para contatar">Contatar</a>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <!-- ARTISTAS -->
      <section class="resultados-bloco" style="margin-top:3rem;">
        <div class="resultados-header">
          <h2>Artistas <span class="counter"><?php echo count($artistas); ?></span></h2>
          <?php if ($termo !== '' && count($artistas) >= 20): ?>
            <a href="Artistas.php?busca=<?php echo urlencode($termo); ?>" class="btn btn-ghost" style="font-size:.8rem;">Ver todos os artistas</a>
          <?php endif; ?>
        </div>

        <?php if ($termo === ''): ?>
          <div class="estado-vazio">
            <p>Digite um termo na busca acima para encontrar artistas.</p>
          </div>
        <?php elseif (empty($artistas)): ?>
          <div class="estado-vazio">
            <p>Nenhum artista encontrado para "<strong><?php echo $termoSeguro; ?></strong>".</p>
            <a href="Artistas.php" class="btn btn-primary">Ver todos os artistas</a>
          </div>
        <?php else: ?>
          <div class="artistas-grid">
            <?php foreach ($artistas as $artista): ?>
              <article class="artist-card-public">
                <div class="artist-photo-wrap">
                  <?php if (!empty($artista['foto_perfil'])): ?>
                    <img class="artist-photo" src="../uploads/perfil/<?php echo htmlspecialchars($artista['foto_perfil']); ?>"
                         alt="Foto de <?php echo htmlspecialchars($artista['nome']); ?>">
                  <?php else: ?>
                    <div class="artist-photo-placeholder">
                      <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="32" cy="24" r="12" stroke="currentColor" stroke-width="2"/>
                        <path d="M8 56c0-13.255 10.745-24 24-24s24 10.745 24 24" stroke="currentColor" stroke-width="2"/>
                      </svg>
                    </div>
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

                  <?php if (!empty($artista['biografia'])): ?>
                  <p class="artist-bio"><?php echo htmlspecialchars(substr($artista['biografia'], 0, 120)); ?>...</p>
                  <?php endif; ?>

                  <div class="artist-contato">
                    <?php if (!empty($artista['valor_hora']) && $artista['valor_hora'] > 0): ?>
                      <span class="artist-valor">R$ <?php echo number_format($artista['valor_hora'], 2, ',', '.'); ?>/h</span>
                    <?php endif; ?>
                    <?php if (!empty($artista['email'])): ?>
                      <a href="mailto:<?php echo htmlspecialchars($artista['email']); ?>" class="contact-link">✉ Email</a>
                    <?php endif; ?>
                    <?php if (!empty($artista['telefone'])): ?>
                      <a href="tel:<?php echo htmlspecialchars(preg_replace('/\D/', '', $artista['telefone'])); ?>" class="contact-link">📞 WhatsApp</a>
                    <?php endif; ?>
                    <?php if (!empty($artista['rede_social'])): ?>
                      <a href="https://instagram.com/<?php echo ltrim($artista['rede_social'], '@'); ?>" target="_blank" class="contact-link">📸 Instagram</a>
                    <?php endif; ?>
                    <a href="PerfilFreelancer.php?id=<?php echo (int) $artista['id_freelancer']; ?>" class="contact-link">Ver Perfil</a>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

    </div>
  </section>
</main>

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>