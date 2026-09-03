<?php
/**
 * Eventos.php — Listagem pública de eventos com busca e filtros
 * Compatível com PHP 5.3.9
 */
session_start();
require_once dirname(__FILE__) . '/../config/conexao.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_eventos.php';

// ---- Filtros (GET) ----
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$categoriaFiltro = isset($_GET['categoria']) && ctype_digit($_GET['categoria']) ? (int) $_GET['categoria'] : 0;
$periodoFiltro = isset($_GET['periodo']) ? $_GET['periodo'] : 'todos'; // todos | proximos | encerrados
$cidadeFiltro = isset($_GET['cidade']) ? trim($_GET['cidade']) : '';
$estadoFiltro = isset($_GET['estado']) ? trim($_GET['estado']) : '';

// ---- Monta query dinâmica ----
$sql = "SELECT e.*, c.nome AS categoria_nome, u.nome AS organizador_nome, u.cidade AS org_cidade, u.estado AS org_estado
        FROM eventos e
        LEFT JOIN categorias_eventos c ON c.id_categoria = e.categoria_id
        LEFT JOIN usuario u ON u.id_usuario = e.usuario_id
        WHERE 1=1";

$tipos = '';
$parametros = array();

if ($busca !== '') {
    $sql .= " AND (e.titulo LIKE ? OR e.descricao LIKE ? OR e.cidade LIKE ?)";
    $tipos .= 'sss';
    $parametros[] = '%' . $busca . '%';
    $parametros[] = '%' . $busca . '%';
    $parametros[] = '%' . $busca . '%';
}

if ($categoriaFiltro > 0) {
    $sql .= " AND e.categoria_id = ?";
    $tipos .= 'i';
    $parametros[] = $categoriaFiltro;
}

if ($periodoFiltro === 'proximos') {
    $sql .= " AND e.data_fim_evento >= CURDATE()";
} elseif ($periodoFiltro === 'encerrados') {
    $sql .= " AND e.data_fim_evento < CURDATE()";
}

if ($cidadeFiltro !== '') {
    $sql .= " AND e.cidade LIKE ?";
    $tipos .= 's';
    $parametros[] = '%' . $cidadeFiltro . '%';
}

if ($estadoFiltro !== '') {
    $sql .= " AND e.estado = ?";
    $tipos .= 's';
    $parametros[] = $estadoFiltro;
}

$sql .= " ORDER BY e.data_inicio_evento ASC";

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

$eventos = array();
while ($linha = $resultado->fetch_assoc()) {
    $eventos[] = $linha;
}
$stmt->close();

$categorias = buscar_categorias_eventos($conexao);

// Busca estados únicos para o filtro
$estados = array();
$resEstados = $conexao->query("SELECT DISTINCT estado FROM eventos ORDER BY estado ASC");
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
  <title>EventoVivo — Todos os Eventos</title>
  <meta name="description" content="Descubra eventos culturais, shows, festivais, teatro e dança na sua cidade.">
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
      <p class="eyebrow">AGENDA CULTURAL</p>
      <h1>EVENTOS</h1>
      <p class="hero-sub">Encontre shows, festivais, teatro, dança e cultura perto de você.</p>
    </div>
  </section>

  <section class="filters-section">
    <div class="wrap">
      <form class="toolbar" method="get" action="Eventos.php">
        <div class="search-box">
          <label for="busca">Buscar</label>
          <input id="busca" type="search" name="busca" placeholder="Evento, descrição, cidade..."
                 value="<?php echo htmlspecialchars($busca); ?>">
        </div>

        <div class="filter-box">
          <label for="categoria">Categoria</label>
          <select id="categoria" name="categoria">
            <option value="0">Todas</option>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?php echo (int) $cat['id_categoria']; ?>"
                <?php echo ($categoriaFiltro === (int) $cat['id_categoria']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat['nome']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-box">
          <label for="periodo">Período</label>
          <select id="periodo" name="periodo">
            <option value="todos" <?php echo ($periodoFiltro === 'todos') ? 'selected' : ''; ?>>Todos</option>
            <option value="proximos" <?php echo ($periodoFiltro === 'proximos') ? 'selected' : ''; ?>>Próximos</option>
            <option value="encerrados" <?php echo ($periodoFiltro === 'encerrados') ? 'selected' : ''; ?>>Encerrados</option>
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
        <a class="btn btn-ghost" href="Eventos.php" style="align-self:flex-end;">LIMPAR</a>
      </form>
    </div>
  </section>

  <section class="results-section">
    <div class="wrap">
      <div class="results-header">
        <h2><?php echo count($eventos); ?> evento(s) encontrado(s)</h2>
      </div>

      <?php if (empty($eventos)): ?>
        <div class="empty-state">
          <p>Nenhum evento encontrado com esses filtros.</p>
          <a class="btn btn-primary" href="Eventos.php">Ver todos os eventos</a>
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
                      <a class="btn-icone" href="#contato" onclick="alert('Faça login para entrar em contato com o organizador.')">Contatar</a>
                    <?php endif; ?>
                  </div>
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