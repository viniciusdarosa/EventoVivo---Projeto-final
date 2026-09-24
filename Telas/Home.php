<?php
/* ==========================================================
 * COMPONENTE: Home.php
 * Página inicial do EventoVivo. Reúne os destaques de eventos e artistas e apresenta a entrada principal para as funcionalidades do sistema.
 * ========================================================== */

require_once dirname(__FILE__) . '/../config/conexao.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_eventos.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_freelancers.php';
require_once dirname(__FILE__) . '/Componentes/cards.php';

/* ---- Busca 4 eventos mais próximos (próximos a acontecer) ---- */
$sqlEventos = "SELECT e.*, c.nome AS categoria_nome
               FROM eventos e
               LEFT JOIN categorias_eventos c ON c.id_categoria = e.categoria_id
               WHERE e.data_fim_evento >= CURDATE()
               ORDER BY e.data_inicio_evento ASC
               LIMIT 4";
$stmtEventos = $conexao->prepare($sqlEventos);
$stmtEventos->execute();
$resultEventos = $stmtEventos->get_result();

$eventos = array();
while ($linha = $resultEventos->fetch_assoc()) {
    $eventos[] = array(
        'data'   => formatar_data_evento($linha['data_inicio_evento']),
        'local'  => $linha['cidade'] . '/' . $linha['estado'],
        'titulo' => $linha['titulo'],
        'desc'   => substr($linha['descricao'], 0, 120) . (strlen($linha['descricao']) > 120 ? '...' : ''),
        'imagem' => evento_imagem_src($linha['imagem_capa']),
    );
}
$stmtEventos->close();

/* ---- Busca 4 artistas melhores avaliados ---- */
$sqlArtistas = "SELECT f.*, u.nome, u.foto_perfil, u.cidade, u.estado,
                       cs.nome AS categoria_nome,
                       COALESCE(AVG(a.nota), 0) AS media_avaliacao,
                       COUNT(a.id) AS total_avaliacoes
                FROM freelancers f
                LEFT JOIN usuario u ON u.id_usuario = f.usuario_id
                LEFT JOIN categorias_servicos cs ON cs.id_categoria = f.categoria_id
                LEFT JOIN avaliacoes a ON a.freelancer_id = f.id_freelancer
                WHERE f.usuario_id IS NOT NULL
                GROUP BY f.id_freelancer, u.id_usuario, cs.id_categoria
                HAVING total_avaliacoes > 0
                ORDER BY media_avaliacao DESC, total_avaliacoes DESC
                LIMIT 4";
$stmtArtistas = $conexao->prepare($sqlArtistas);
$stmtArtistas->execute();
$resultArtistas = $stmtArtistas->get_result();

$artistas = array();
while ($linha = $resultArtistas->fetch_assoc()) {
    $artistas[] = array(
        'foto'       => freelancer_imagem_publica_src($linha['foto_perfil'], $linha['portfolio']),
        'nome'       => $linha['nome'],
        'tipo'       => $linha['profissao'] ?: $linha['categoria_nome'],
        'local'      => $linha['cidade'] . '/' . $linha['estado'],
        'categorias' => array(),
    );
}
$stmtArtistas->close();

require dirname(__FILE__) . '/Componentes/header.php';
?>

<main>

  <section class="hero" id="topo">
    <div class="wrap">
      <p class="eyebrow">Sem gravadora. Sem empresário.</p>
      <h1>A cena independente tem endereço.</h1>
      <p class="hero-sub">EventoVivo junta artistas e público num só lugar. Divulgue seu trabalho, publique seu show ou ache o próximo point da cidade — sem burocracia, sem intermediário.</p>
      <div class="hero-cta">
        <a class="btn btn-primary" href="#como-funciona">Divulgar meu trabalho</a>
        <a class="btn btn-ghost" href="Eventos.php">Ver todos os eventos</a>
      </div>
    </div>
  </section>

  <section class="how" id="como-funciona">
    <div class="wrap">
      <h2>Como funciona</h2>
      <ol class="steps">
        <li>
          <span class="num">01</span>
          <h3>Crie seu perfil</h3>
          <p>Artista sobe fotos, bio, agenda e links em poucos minutos.</p>
        </li>
        <li>
          <span class="num">02</span>
          <h3>Publique o evento</h3>
          <p>Data, local e ingresso: seu show fica visível pra cidade toda.</p>
        </li>
        <li>
          <span class="num">03</span>
          <h3>Pesquise e descubra</h3>
          <p>Quem procura entretenimento acha artistas e eventos pela busca.</p>
        </li>
        <li>
          <span class="num">04</span>
          <h3>Feche negócio</h3>
          <p>Contato direto entre artista, produtor e público, sem intermediário.</p>
        </li>
      </ol>
    </div>
  </section>

  <section class="destaque" id="eventos">
    <div class="wrap">
      <div class="section-header">
        <h2>Próximos eventos</h2>
        <a class="btn btn-ghost" href="Eventos.php" style="font-size:.85rem;padding:.6rem 1rem;">Ver todos</a>
      </div>
      <div class="grid">
        <?php if (empty($eventos)): ?>
          <p class="estado-vazio">Nenhum evento próximo cadastrado.</p>
        <?php else: ?>
          <?php foreach ($eventos as $evento): ?>
            <?php render_event_card($evento); ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="destaque alt" id="artistas">
    <div class="wrap">
      <div class="section-header">
        <h2>Artistas mais bem avaliados</h2>
        <a class="btn btn-ghost" href="Artistas.php" style="font-size:.85rem;padding:.6rem 1rem;">Ver todos</a>
      </div>
      <div class="grid">
        <?php if (empty($artistas)): ?>
          <p class="estado-vazio">Nenhum artista avaliado ainda.</p>
        <?php else: ?>
          <?php foreach ($artistas as $artista): ?>
            <?php render_artist_card($artista); ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

</main>

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>