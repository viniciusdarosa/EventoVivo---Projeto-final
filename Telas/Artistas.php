<?php
/**
 * Artistas.php — Listagem pública de artistas/freelancers com busca e filtros
 * Compatível com PHP 5.3.9
 */
session_start();
require_once dirname(__FILE__) . '/../config/conexao.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_eventos.php';

// ---- Filtros (GET) ----
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$categoriaFiltro = isset($_GET['categoria']) && ctype_digit($_GET['categoria']) ? (int) $_GET['categoria'] : 0;
$cidadeFiltro = isset($_GET['cidade']) ? trim($_GET['cidade']) : '';
$estadoFiltro = isset($_GET['estado']) ? trim($_GET['estado']) : '';

// ---- Busca categorias de serviços ----
$categoriasServicos = array();
$resCat = $conexao->query("SELECT id_categoria, nome FROM categorias_servicos ORDER BY nome ASC");
if ($resCat) {
    while ($row = $resCat->fetch_assoc()) {
        $categoriasServicos[] = $row;
    }
    $resCat->free();
}

// ---- Monta query dinâmica ----
$sql = "SELECT f.*, u.nome, u.foto_perfil, u.biografia, u.cidade, u.estado, u.email, u.telefone,
               cs.nome AS categoria_nome
        FROM freelancers f
        LEFT JOIN usuario u ON u.id_usuario = f.usuario_id
        LEFT JOIN categorias_servicos cs ON cs.id_categoria = f.categoria_id
        WHERE 1=1";

$tipos = '';
$parametros = array();

if ($busca !== '') {
    $sql .= " AND (u.nome LIKE ? OR f.profissao LIKE ? OR f.descricao LIKE ? OR f.experiencia LIKE ? OR u.cidade LIKE ?)";
    $tipos .= 'sssss';
    $parametros[] = '%' . $busca . '%';
    $parametros[] = '%' . $busca . '%';
    $parametros[] = '%' . $busca . '%';
    $parametros[] = '%' . $busca . '%';
    $parametros[] = '%' . $busca . '%';
}

if ($categoriaFiltro > 0) {
    $sql .= " AND f.categoria_id = ?";
    $tipos .= 'i';
    $parametros[] = $categoriaFiltro;
}

if ($cidadeFiltro !== '') {
    $sql .= " AND u.cidade LIKE ?";
    $tipos .= 's';
    $parametros[] = '%' . $cidadeFiltro . '%';
}

if ($estadoFiltro !== '') {
    $sql .= " AND u.estado = ?";
    $tipos .= 's';
    $parametros[] = $estadoFiltro;
}

$sql .= " ORDER BY u.nome ASC";

$stmt = $conexao->prepare($sql);

if ($stmt === false) {
    die('Erro ao preparar query: ' . $conexao->error);
}

// bind_param dinâmico
if ($tipos !== '') {
    $paramsPorReferencia = array($tipos);
    for ($i = 0; $i < count($parametros); $i++) {
        $paramsPorReferencia[] = &$parametros[$i];
    }
    call_user_func_array(array($stmt, 'bind_param'), $paramsPorReferencia);
}

$stmt->execute();
$resultado = $stmt->get_result();

$artistas = array();
while ($linha = $resultado->fetch_assoc()) {
    $artistas[] = $linha;
}
$stmt->close();

// Busca estados únicos para o filtro
$estados = array();
$resEstados = $conexao->query("SELECT DISTINCT estado FROM usuario WHERE tipo IN ('usuario','empresa') ORDER BY estado ASC");
if ($resEstados) {
    while ($row = $resEstados->fetch_assoc()) {
        $estados[] = $row['estado'];
    }
    $resEstados->free();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventoVivo — Artistas e Freelancers</title>
  <meta name="description" content="Encontre músicos, fotógrafos, videomakers, dançarinos, designers e outros artistas para seu evento.">
  <meta name="theme-color" content="#110f0c">
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' fill='%23110f0c'/%3E%3Ctext x='32' y='46' font-family='Arial, sans-serif' font-weight='900' font-size='38' fill='%23b8000d' text-anchor='middle'%3EV%3C/text%3E%3C/svg%3E">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Courier+Prime:wght@400;700&family=Permanent+Marker&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../Css/style.css">
</head>
<?php require dirname(__FILE__) . '/Componentes/header.php'; ?>
<body>

<main class="list-page">
  <section class="page-header">
    <div class="wrap">
      <p class="eyebrow">Cena Independente</p>
      <h1>ARTISTAS</h1>
      <p class="hero-sub">Músicos, fotógrafos, videomakers, dançarinos, designers e mais. Contrate direto, sem intermediário.</p>
    </div>
  </section>

  <section class="filters-section">
    <div class="wrap">
      <form class="toolbar" method="get" action="Artistas.php">
        <div class="search-box">
          <label for="busca">Buscar</label>
          <input id="busca" type="search" name="busca" placeholder="Nome, especialidade, cidade..."
                 value="<?php echo htmlspecialchars($busca); ?>">
        </div>

        <div class="filter-box">
          <label for="categoria">Categoria</label>
          <select id="categoria" name="categoria">
            <option value="0">Todas</option>
            <?php foreach ($categoriasServicos as $cat): ?>
              <option value="<?php echo (int) $cat['id_categoria']; ?>"
                <?php echo ($categoriaFiltro === (int) $cat['id_categoria']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat['nome']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-box">
          <label for="cidade">Cidade</label>
          <input type="text" id="cidade" name="cidade" placeholder="Cidade"
                 value="<?php echo htmlspecialchars($cidadeFiltro); ?>">
        </div>

        <div class="filter-box">
          <label for="estado">Estado</label>
          <select id="estado" name="estado">
            <option value="">Todos</option>
            <?php foreach ($estados as $uf): ?>
              <option value="<?php echo htmlspecialchars($uf); ?>"
                <?php echo ($estadoFiltro === $uf) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($uf); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <button class="btn btn-filter" type="submit">FILTRAR</button>
        <a class="btn btn-ghost" href="Artistas.php" style="align-self:flex-end;">LIMPAR</a>
      </form>
    </div>
  </section>

  <section class="results-section">
    <div class="wrap">
      <div class="results-header">
        <h2><?php echo count($artistas); ?> artista(s) encontrado(s)</h2>
      </div>

      <?php if (empty($artistas)): ?>
        <div class="empty-state">
          <p>Nenhum artista encontrado com esses filtros.</p>
          <a class="btn btn-primary" href="Artistas.php">Ver todos os artistas</a>
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
</body>
</html>