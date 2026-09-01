<?php
/**
 * CRUD_Eventos.php
 *
 * Lista os eventos cadastrados pelo usuário logado (imagem de capa,
 * título, data/hora, cidade, valor, vagas), com busca por texto e
 * filtro por categoria/período, além dos botões de Editar e Excluir
 * de cada evento.
 */

session_start();
require_once dirname(__FILE__) . '/../config/conexao.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_eventos.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuarioId = (int) $_SESSION['id_usuario'];

// ---- Filtros vindos da barra de busca (GET, para poder compartilhar/recarregar a URL) ----
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$categoriaFiltro = isset($_GET['categoria']) && ctype_digit($_GET['categoria']) ? (int) $_GET['categoria'] : 0;
$periodoFiltro = isset($_GET['periodo']) ? $_GET['periodo'] : 'todos'; // todos | proximos | encerrados

// ---- Monta a query dinamicamente, sempre com parâmetros (nunca concatenando valor direto do usuário) ----
$sql = "SELECT e.*, c.nome AS categoria_nome
        FROM eventos e
        LEFT JOIN categorias_eventos c ON c.id_categoria = e.categoria_id
        WHERE e.usuario_id = ?";

$tipos = 'i';
$parametros = array($usuarioId);

if ($busca !== '') {
    $sql .= " AND e.titulo LIKE ?";
    $tipos .= 's';
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

$sql .= " ORDER BY e.data_inicio_evento ASC";

$stmt = $conexao->prepare($sql);

// bind_param precisa dos argumentos por referência — monta isso dinamicamente.
$paramsPorReferencia = array($tipos);
for ($i = 0; $i < count($parametros); $i++) {
    $paramsPorReferencia[] = &$parametros[$i];
}
call_user_func_array(array($stmt, 'bind_param'), $paramsPorReferencia);

$stmt->execute();
$resultado = $stmt->get_result();

$eventos = array();
while ($linha = $resultado->fetch_assoc()) {
    $eventos[] = $linha;
}
$stmt->close();

$categorias = buscar_categorias_eventos($conexao);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventoVivo — Gerenciar eventos</title>
  <meta name="theme-color" content="#110f0c">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Courier+Prime:wght@400;700&family=Permanent+Marker&display=swap" rel="stylesheet">

  <!-- Ajuste o caminho caso este arquivo fique em outra pasta -->
  <link rel="stylesheet" href="../Css/crud_eventos.css">
</head>
<?php
require dirname(__FILE__) . '/Componentes/header.php';
?>
<body>



<main class="admin-page">

  <section class="page-heading">
    <div class="wrap heading-inner">
      <div>
        <p class="eyebrow">PAINEL DE CONTROLE</p>
        <h1>MEUS EVENTOS</h1>
        <p class="heading-sub">
          Gerencie os eventos publicados pela sua conta.
        </p>
      </div>

      <a class="btn btn-primary" href="CadastrarEvento.php">
        + NOVO EVENTO
      </a>
    </div>
  </section>

  <section class="events-section">
    <div class="wrap">

      <form class="toolbar" method="get" action="CRUD_Eventos.php">
        <div class="search-box">
          <label for="busca">Buscar evento</label>
          <input id="busca" type="search" name="busca" placeholder="Nome do evento..."
                 value="<?php echo htmlspecialchars($busca); ?>">
        </div>

        <div class="filter-box">
          <label for="categoria">Categoria</label>
          <select id="categoria" name="categoria">
            <option value="0">Todas as categorias</option>
            <?php foreach ($categorias as $categoria): ?>
              <option value="<?php echo (int) $categoria['id_categoria']; ?>"
                <?php echo ($categoriaFiltro === (int) $categoria['id_categoria']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($categoria['nome']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-box">
          <label for="periodo">Período</label>
          <select id="periodo" name="periodo">
            <option value="todos" <?php echo ($periodoFiltro === 'todos') ? 'selected' : ''; ?>>Todos</option>
            <option value="proximos" <?php echo ($periodoFiltro === 'proximos') ? 'selected' : ''; ?>>Próximos eventos</option>
            <option value="encerrados" <?php echo ($periodoFiltro === 'encerrados') ? 'selected' : ''; ?>>Eventos encerrados</option>
          </select>
        </div>

        <button class="btn btn-filter" type="submit">FILTRAR</button>
      </form>

      <div class="list-header">
        <div>
          <h2>EVENTOS PUBLICADOS</h2>
          <span class="counter"><?php echo count($eventos); ?> evento(s)</span>
        </div>
      </div>

      <?php if (empty($eventos)): ?>

        <p class="estado-vazio">
          Nenhum evento encontrado.
          <a href="CadastrarEvento.php">Cadastre o primeiro evento</a>.
        </p>

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

                <p class="evento-card-info">
                  <?php echo (int) $evento['vagas']; ?> vaga(s)
                </p>

                <div class="evento-card-rodape">
                  <span class="evento-card-valor"><?php echo formatar_valor_evento($evento['valor']); ?></span>

                  <div class="evento-card-acoes">
                    <a class="btn-icone" href="EditarEvento.php?id_evento=<?php echo (int) $evento['id_evento']; ?>">Editar</a>

                    <form method="post" action="ExcluirEvento.php"
                          onsubmit="return confirm('Tem certeza que deseja excluir este evento? Essa ação não pode ser desfeita.');"
                          style="display:inline;">
                      <input type="hidden" name="id_evento" value="<?php echo (int) $evento['id_evento']; ?>">
                      <button type="submit" class="btn-icone btn-icone-excluir">Excluir</button>
                    </form>
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
