<?php
/* ==========================================================
 * COMPONENTE: Eventos.php
 * Lista publicamente os eventos cadastrados e oferece busca e filtros por categoria, período e localização.
 * ========================================================== */

/**
 * Eventos.php — Listagem pública de eventos com busca e filtros
 */
require_once dirname(__FILE__) . '/../config/bootstrap.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_eventos.php';
require_once dirname(__FILE__) . '/Componentes/EventoCard.php';

// A listagem é somente leitura: nada é arquivado nem apagado aqui.
// O arquivamento é uma ação explícita do dono (CRUD_Eventos.php,
// botão "Arquivar"), nunca disparada por navegação.

// ---- Filtros (GET) — entrada_texto/entrada_inteiro rejeitam arrays ----
$busca = entrada_texto($_GET, 'busca');
$categoriaFiltro = entrada_inteiro($_GET, 'categoria');
$periodoFiltro = entrada_texto($_GET, 'periodo', 'todos'); // todos | proximos | encerrados
$cidadeFiltro = entrada_texto($_GET, 'cidade');
$estadoFiltro = entrada_texto($_GET, 'estado');

// ---- Monta query dinâmica ----
// u.e-mail/telefone alimentam o botão "Contatar organizador" do card:
// o contato é do usuário DONO do evento (e.usuario_id → usuario).
$sql = "SELECT e.*, c.nome AS categoria_nome, u.nome AS organizador_nome, u.cidade AS org_cidade, u.estado AS org_estado,
               u.email AS organizador_email, u.telefone AS organizador_telefone
        FROM eventos e
        LEFT JOIN categorias_eventos c ON c.id_categoria = e.categoria_id
        LEFT JOIN usuario u ON u.id_usuario = e.usuario_id
        WHERE e.arquivado = 0";

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
    registrar_log('Falha ao preparar listagem de eventos: ' . $conexao->error, 'ERROR');
    $eventos = array();
    $stmt = null;
}

if ($stmt !== null) {
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
}

$categorias = buscar_categorias_eventos($conexao);

// Busca estados únicos para o filtro
$estados = array();
$resEstados = $conexao->query("SELECT DISTINCT estado FROM eventos WHERE arquivado = 0 AND estado IS NOT NULL ORDER BY estado ASC");
if ($resEstados) {
    while ($row = $resEstados->fetch_assoc()) {
        $estados[] = $row['estado'];
    }
    $resEstados->free();
}
$pageTitle = 'EventoVivo — Todos os Eventos';
$pageDescription = 'Descubra eventos culturais, shows, festivais, teatro e dança na sua cidade.';
$extraCss = array();

require dirname(__FILE__) . '/Componentes/header.php';
?>

<main class="eventos-page">
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
        <a class="btn btn-ghost" href="Eventos.php">LIMPAR</a>
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
            <?php render_evento_card($evento); ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>