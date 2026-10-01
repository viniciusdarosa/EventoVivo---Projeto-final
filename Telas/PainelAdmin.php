<?php
/* ==========================================================
 * COMPONENTE: PainelAdmin.php
 * Painel administrativo do ADM: visão geral do uso do site,
 * artistas por categoria, eventos cadastrados, ranking de
 * artistas e filtros por período, cidade/UF e tipo de conta.
 * ========================================================== */

/**
 * PainelAdmin.php
 *
 * Acesso restrito a usuários com tipo = 'admin' na tabela usuario.
 * Qualquer outro tipo é redirecionado para o painel de eventos.
 */

session_start();
require_once dirname(__FILE__) . '/../config/conexao.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_eventos.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_freelancers.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_admin.php';

admin_exigir_permissao($conexao);

// Guarda a visita no histórico usado pelo indicador de uso do site.
admin_registrar_acesso($conexao, (int) $_SESSION['id_usuario'], 'PainelAdmin.php');

// ==========================================================
//  FILTROS
// ==========================================================

// Filtro geral (barra principal do painel).
$filtros = admin_ler_filtros();

// Filtro da tabela de eventos.
$busca = isset($_GET['busca']) && !is_array($_GET['busca']) ? trim($_GET['busca']) : '';
$categoriaEvento = admin_int_de_get('categoria_evento', 0);

$situacoes = array('todos', 'proximos', 'encerrados');
$situacao = isset($_GET['situacao']) && in_array($_GET['situacao'], $situacoes) ? $_GET['situacao'] : 'todos';

$ordens = array('recentes', 'inicio', 'titulo', 'vagas');
$ordem = isset($_GET['ordem']) && in_array($_GET['ordem'], $ordens) ? $_GET['ordem'] : 'recentes';

// Filtro do ranking de artistas.
$categoriaArtista = admin_int_de_get('categoria_artista', 0);

$criterios = array('avaliacao', 'avaliacoes', 'favoritos', 'cadastros');
$criterio = isset($_GET['criterio']) && in_array($_GET['criterio'], $criterios) ? $_GET['criterio'] : 'avaliacao';

$periodosRotulo = array(
    7 => 'Últimos 7 dias',
    30 => 'Últimos 30 dias',
    90 => 'Últimos 90 dias',
    0 => 'Todo o histórico',
);

$periodoRotulo = isset($periodosRotulo[$filtros['periodo']])
    ? $periodosRotulo[$filtros['periodo']]
    : $periodosRotulo[30];

// ==========================================================
//  CONSULTA DO PAINEL
// ==========================================================

$categoriasServicos = buscar_categorias_servicos($conexao);
$categoriasEventos = buscar_categorias_eventos($conexao);
$ufs = admin_lista_ufs($conexao);

$acesso = admin_indicador_acesso($conexao, $filtros);
$usuarios = admin_indicador_usuarios($conexao, $filtros);
$artistasPorCategoria = admin_artistas_por_categoria($conexao, $filtros);
$eventosIndicador = admin_indicador_eventos($conexao, $filtros);
$social = admin_indicadores_sociais($conexao);

$listaEventos = admin_listar_eventos($conexao, $filtros, $busca, $categoriaEvento, $situacao, $ordem, 40);
$ranking = admin_ranking_artistas($conexao, $filtros, $categoriaArtista, $criterio, 15);

// Totais usados nas barras dos gráficos.
$maxArtistas = 0;
$totalArtistas = 0;
foreach ($artistasPorCategoria as $categoria) {
    if ($categoria['total'] > $maxArtistas) {
        $maxArtistas = $categoria['total'];
    }
    $totalArtistas += $categoria['total'];
}

$maxEventos = 0;
foreach ($eventosIndicador['por_categoria'] as $categoria) {
    if ($categoria['total'] > $maxEventos) {
        $maxEventos = $categoria['total'];
    }
}

$totalFiltroAtivo = 0;
if ($filtros['periodo'] > 0) {
    $totalFiltroAtivo++;
}
if ($filtros['uf'] !== '') {
    $totalFiltroAtivo++;
}
if ($filtros['cidade'] !== '') {
    $totalFiltroAtivo++;
}
if ($filtros['tipo'] !== 'todos') {
    $totalFiltroAtivo++;
}

$pageTitle = 'EventoVivo — Painel do ADM';
$pageDescription = 'Painel administrativo do EventoVivo: uso do site, artistas por categoria, eventos e ranking.';
$extraCss = array('../Css/painel_admin.css');

require dirname(__FILE__) . '/Componentes/header.php';
?>

<main class="adm-page">

  <section class="adm-heading">
    <div class="adm-wrap">
      <p class="eyebrow">PAINEL ADMINISTRATIVO</p>
      <h1>Controle do EventoVivo</h1>
      <p class="heading-sub">
        Quantas pessoas estão usando o site, como os artistas se distribuem por
        categoria, os eventos cadastrados e o ranking da cena.
      </p>

      <div class="adm-heading-meta">
        <span>Administrador: <strong><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></strong></span>
        <span>Período: <strong><?php echo htmlspecialchars($periodoRotulo); ?></strong></span>
        <span>Filtros ativos: <strong><?php echo (int) $totalFiltroAtivo; ?></strong></span>
      </div>
    </div>
  </section>

  <div class="adm-wrap">

    <!-- ======================================================
         FILTROS GERAIS DO PAINEL
         ====================================================== -->
    <form class="adm-filters" method="get" action="PainelAdmin.php">
      <div class="campo">
        <label for="periodo">Período de uso</label>
        <select id="periodo" name="periodo">
          <option value="7" <?php echo ($filtros['periodo'] === 7) ? 'selected' : ''; ?>>Últimos 7 dias</option>
          <option value="30" <?php echo ($filtros['periodo'] === 30) ? 'selected' : ''; ?>>Últimos 30 dias</option>
          <option value="90" <?php echo ($filtros['periodo'] === 90) ? 'selected' : ''; ?>>Últimos 90 dias</option>
          <option value="0" <?php echo ($filtros['periodo'] === 0) ? 'selected' : ''; ?>>Todo o histórico</option>
        </select>
      </div>

      <div class="campo">
        <label for="uf">Estado</label>
        <select id="uf" name="uf">
          <option value="">Todos os estados</option>
          <?php foreach ($ufs as $uf): ?>
            <option value="<?php echo htmlspecialchars($uf); ?>"
              <?php echo ($filtros['uf'] === $uf) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($uf); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="campo">
        <label for="cidade">Cidade</label>
        <input id="cidade" type="text" name="cidade" placeholder="Ex.: Criciúma"
               value="<?php echo htmlspecialchars($filtros['cidade']); ?>">
      </div>

      <div class="campo">
        <label for="tipo">Tipo de conta</label>
        <select id="tipo" name="tipo">
          <option value="todos" <?php echo ($filtros['tipo'] === 'todos') ? 'selected' : ''; ?>>Todas</option>
          <option value="usuario" <?php echo ($filtros['tipo'] === 'usuario') ? 'selected' : ''; ?>>Usuários</option>
          <option value="empresa" <?php echo ($filtros['tipo'] === 'empresa') ? 'selected' : ''; ?>>Empresas</option>
          <option value="admin" <?php echo ($filtros['tipo'] === 'admin') ? 'selected' : ''; ?>>Administradores</option>
        </select>
      </div>

      <div class="adm-filters-acoes">
        <button class="adm-btn" type="submit">Filtrar</button>
        <a class="adm-btn adm-btn-fantasma" href="PainelAdmin.php">Limpar</a>
      </div>
    </form>

    <!-- ======================================================
         INDICADORES GERAIS
         ====================================================== -->
    <section class="adm-kpis">

      <article class="adm-kpi adm-kpi-destaque">
        <p class="adm-kpi-rotulo">Pessoas ativas</p>
        <p class="adm-kpi-valor"><?php echo (int) $acesso['ativos']; ?></p>
        <p class="adm-kpi-nota">
          <?php echo htmlspecialchars($periodoRotulo); ?> ·
          média de <b><?php echo number_format($acesso['media_por_usuario'], 1, ',', '.'); ?></b> acessos por pessoa
        </p>
      </article>

      <article class="adm-kpi">
        <p class="adm-kpi-rotulo">Acessos</p>
        <p class="adm-kpi-valor"><?php echo (int) $acesso['acessos']; ?></p>
        <p class="adm-kpi-nota">
          no período ·
          <b><?php echo (int) $acesso['acessos'] - (int) $acesso['ativos']; ?></b> sem cadastro
        </p>
      </article>

      <article class="adm-kpi">
        <p class="adm-kpi-rotulo">Contas</p>
        <p class="adm-kpi-valor"><?php echo (int) $usuarios['total']; ?></p>
        <p class="adm-kpi-nota">
          <b><?php echo (int) $usuarios['usuarios']; ?></b> usuário(s) ·
          <b><?php echo (int) $usuarios['empresas']; ?></b> empresa(s) ·
          <b><?php echo (int) $usuarios['admins']; ?></b> admin(s)
        </p>
      </article>

      <article class="adm-kpi">
        <p class="adm-kpi-rotulo">Artistas</p>
        <p class="adm-kpi-valor"><?php echo (int) $social['artistas']; ?></p>
        <p class="adm-kpi-nota">
          cadastrados ·
          <b><?php echo (int) $totalArtistas; ?></b> no filtro atual
        </p>
      </article>

      <article class="adm-kpi">
        <p class="adm-kpi-rotulo">Eventos</p>
        <p class="adm-kpi-valor"><?php echo (int) $eventosIndicador['total']; ?></p>
        <p class="adm-kpi-nota">
          <b><?php echo (int) $eventosIndicador['proximos']; ?></b> a acontecer ·
          <b><?php echo (int) $eventosIndicador['encerrados']; ?></b> encerrado(s)
        </p>
      </article>

    </section>

    <!-- ======================================================
         PÚBLICO POR CIDADE
         ====================================================== -->
    <section class="adm-secao">
      <div class="adm-secao-topo">
        <h2>Contas por cidade</h2>
        <p class="dica">Onde estão as <?php echo (int) $usuarios['total']; ?> contas do filtro atual</p>
      </div>

      <?php if (empty($usuarios['por_cidade'])): ?>
        <p class="adm-vazio">Nenhuma conta encontrada para o filtro selecionado.</p>
      <?php else: ?>
        <ul class="adm-cidades">
          <?php foreach ($usuarios['por_cidade'] as $cidade): ?>
            <li>
              <span class="adm-cidade-nome">
                <?php echo htmlspecialchars($cidade['cidade']); ?><span class="adm-uf">/<?php echo htmlspecialchars($cidade['estado']); ?></span>
              </span>
              <span class="adm-tag"><?php echo (int) $cidade['total']; ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <!-- ======================================================
         ARTISTAS POR CATEGORIA
         ====================================================== -->
    <section class="adm-secao">
      <div class="adm-secao-topo">
        <h2>Artistas por categoria</h2>
        <p class="dica"><?php echo (int) $totalArtistas; ?> perfil(s) no filtro atual</p>
      </div>

      <div class="adm-painel">
        <div class="adm-barras">
          <?php foreach ($artistasPorCategoria as $categoria): ?>
            <div class="adm-barra">
              <span class="adm-barra-rotulo" title="<?php echo htmlspecialchars($categoria['nome']); ?>">
                <?php echo htmlspecialchars($categoria['nome']); ?>
              </span>
              <span class="adm-barra-trilho">
                <span class="adm-barra-preenche"
                      style="width: <?php echo admin_porcentagem($categoria['total'], $maxArtistas); ?>%"></span>
              </span>
              <span class="adm-barra-valor">
                <b><?php echo (int) $categoria['total']; ?></b>
                <span class="adm-barra-sub">
                  <?php echo ((int) $categoria['total'] === 1) ? 'artista' : 'artistas'; ?>
                  <?php if ((int) $categoria['avaliacoes'] > 0): ?>
                    · <?php echo number_format($categoria['media'], 1, ',', '.'); ?> ★
                  <?php endif; ?>
                </span>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- ======================================================
         EVENTOS CADASTRADOS
         ====================================================== -->
    <section class="adm-secao">
      <div class="adm-secao-topo">
        <h2>Eventos cadastrados</h2>
        <p class="dica">
          <?php echo (int) $eventosIndicador['total']; ?> evento(s) ·
          <?php echo (int) $eventosIndicador['vagas']; ?> vaga(s) ·
          <?php echo formatar_valor_evento($eventosIndicador['receita']); ?> em ingressos
        </p>
      </div>

      <form class="adm-subfiltros" method="get" action="PainelAdmin.php">
        <input type="hidden" name="periodo" value="<?php echo (int) $filtros['periodo']; ?>">
        <input type="hidden" name="uf" value="<?php echo htmlspecialchars($filtros['uf']); ?>">
        <input type="hidden" name="cidade" value="<?php echo htmlspecialchars($filtros['cidade']); ?>">
        <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($filtros['tipo']); ?>">

        <div class="campo">
          <label for="busca">Buscar evento</label>
          <input id="busca" type="search" name="busca" placeholder="Título, cidade ou responsável..."
                 value="<?php echo htmlspecialchars($busca); ?>">
        </div>

        <div class="campo">
          <label for="categoria_evento">Categoria</label>
          <select id="categoria_evento" name="categoria_evento">
            <option value="0">Todas</option>
            <?php foreach ($categoriasEventos as $categoria): ?>
              <option value="<?php echo (int) $categoria['id_categoria']; ?>"
                <?php echo ($categoriaEvento === (int) $categoria['id_categoria']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($categoria['nome']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="campo">
          <label for="situacao">Situação</label>
          <select id="situacao" name="situacao">
            <option value="todos" <?php echo ($situacao === 'todos') ? 'selected' : ''; ?>>Todas</option>
            <option value="proximos" <?php echo ($situacao === 'proximos') ? 'selected' : ''; ?>>A acontecer</option>
            <option value="encerrados" <?php echo ($situacao === 'encerrados') ? 'selected' : ''; ?>>Encerrados</option>
          </select>
        </div>

        <div class="campo">
          <label for="ordem">Ordenar por</label>
          <select id="ordem" name="ordem">
            <option value="recentes" <?php echo ($ordem === 'recentes') ? 'selected' : ''; ?>>Publicação mais recente</option>
            <option value="inicio" <?php echo ($ordem === 'inicio') ? 'selected' : ''; ?>>Data de início</option>
            <option value="titulo" <?php echo ($ordem === 'titulo') ? 'selected' : ''; ?>>Título (A–Z)</option>
            <option value="vagas" <?php echo ($ordem === 'vagas') ? 'selected' : ''; ?>>Vagas</option>
          </select>
        </div>

        <button class="adm-btn" type="submit">Filtrar</button>
        <a class="adm-btn adm-btn-fantasma" href="<?php echo htmlspecialchars(admin_link_com_filtros($filtros)); ?>">Limpar</a>
      </form>

      <?php if (empty($listaEventos)): ?>

        <p class="adm-vazio">Nenhum evento encontrado com os filtros selecionados.</p>

      <?php else: ?>

        <div class="adm-tabela-envolve">
          <table class="adm-tabela">
            <caption><?php echo count($listaEventos); ?> evento(s) listado(s) — máximo de 40 por consulta</caption>
            <thead>
              <tr>
                <th>Evento</th>
                <th>Categoria</th>
                <th>Período</th>
                <th>Cidade</th>
                <th>Publicado por</th>
                <th class="adm-num">Vagas</th>
                <th class="adm-num">Favoritos</th>
                <th class="adm-num">Valor</th>
                <th>Situação</th>
              </tr>
            </thead>
<tbody>
                <?php foreach ($listaEventos as $evento): ?>
                <tr>
                  <td class="principal">
                    <?php echo htmlspecialchars($evento['titulo']); ?>
                    <span class="secundario">#<?php echo (int) $evento['id_evento']; ?></span>
                  </td>
                  <td data-label="Categoria"><?php echo htmlspecialchars($evento['categoria']); ?></td>
                  <td data-label="Período">
                    <?php echo formatar_data_evento($evento['data_inicio_evento']); ?>
                    <?php if ($evento['data_fim_evento'] !== $evento['data_inicio_evento']): ?>
                      <span class="secundario">até <?php echo formatar_data_evento($evento['data_fim_evento']); ?></span>
                    <?php endif; ?>
                  </td>
                  <td data-label="Cidade"><?php echo htmlspecialchars($evento['cidade']); ?>/<?php echo htmlspecialchars($evento['estado']); ?></td>
                  <td data-label="Publicado por"><?php echo htmlspecialchars($evento['publicado_por']); ?></td>
                  <td class="adm-num" data-label="Vagas"><?php echo (int) $evento['vagas']; ?></td>
                  <td class="adm-num" data-label="Favoritos"><?php echo (int) $evento['favoritos']; ?></td>
                  <td class="adm-num" data-label="Valor"><?php echo formatar_valor_evento($evento['valor']); ?></td>
                  <td data-label="Situação">
                    <?php if ($evento['ativo']): ?>
                      <span class="adm-tag adm-tag-ativa">A acontecer</span>
                    <?php else: ?>
                      <span class="adm-tag adm-tag-encerrada">Encerrado</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
          </table>
        </div>

      <?php endif; ?>

      <div class="adm-secao-topo adm-secao-topo-apertado">
        <h2>Eventos por categoria</h2>
      </div>

      <div class="adm-painel">
        <div class="adm-barras">
          <?php foreach ($eventosIndicador['por_categoria'] as $categoria): ?>
            <div class="adm-barra">
              <span class="adm-barra-rotulo"><?php echo htmlspecialchars($categoria['nome']); ?></span>
              <span class="adm-barra-trilho">
                <span class="adm-barra-preenche"
                      style="width: <?php echo admin_porcentagem($categoria['total'], $maxEventos); ?>%"></span>
              </span>
              <span class="adm-barra-valor">
                <b><?php echo (int) $categoria['total']; ?></b>
                <span class="adm-barra-sub">
                  <?php echo ((int) $categoria['total'] === 1) ? 'evento' : 'eventos'; ?>
                  · <?php echo (int) $categoria['proximos']; ?> ativo(s)
                </span>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- ======================================================
         RANKING DE ARTISTAS
         ====================================================== -->
    <section class="adm-secao">
      <div class="adm-secao-topo">
        <h2>Ranking de artistas</h2>
        <p class="dica">Top 15 do filtro atual</p>
      </div>

      <form class="adm-subfiltros" method="get" action="PainelAdmin.php">
        <input type="hidden" name="periodo" value="<?php echo (int) $filtros['periodo']; ?>">
        <input type="hidden" name="uf" value="<?php echo htmlspecialchars($filtros['uf']); ?>">
        <input type="hidden" name="cidade" value="<?php echo htmlspecialchars($filtros['cidade']); ?>">
        <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($filtros['tipo']); ?>">

        <div class="campo">
          <label for="categoria_artista">Categoria</label>
          <select id="categoria_artista" name="categoria_artista">
            <option value="0">Todas as categorias</option>
            <?php foreach ($categoriasServicos as $categoria): ?>
              <option value="<?php echo (int) $categoria['id_categoria']; ?>"
                <?php echo ($categoriaArtista === (int) $categoria['id_categoria']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($categoria['nome']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="campo">
          <label for="criterio">Critério do ranking</label>
          <select id="criterio" name="criterio">
            <option value="avaliacao" <?php echo ($criterio === 'avaliacao') ? 'selected' : ''; ?>>Nota média das avaliações</option>
            <option value="avaliacoes" <?php echo ($criterio === 'avaliacoes') ? 'selected' : ''; ?>>Quantidade de avaliações</option>
            <option value="favoritos" <?php echo ($criterio === 'favoritos') ? 'selected' : ''; ?>>Favoritos recebidos</option>
            <option value="cadastros" <?php echo ($criterio === 'cadastros') ? 'selected' : ''; ?>>Mais recentes</option>
          </select>
        </div>

        <button class="adm-btn" type="submit">Filtrar</button>
        <a class="adm-btn adm-btn-fantasma" href="<?php echo htmlspecialchars(admin_link_com_filtros($filtros)); ?>">Limpar</a>
      </form>

      <?php if (empty($ranking)): ?>

        <p class="adm-vazio">Nenhum artista encontrado com os filtros selecionados.</p>

      <?php else: ?>

        <div class="adm-tabela-envolve">
          <table class="adm-tabela">
            <caption>
              <?php echo count($ranking); ?> artista(s) no ranking — clique no nome para abrir o perfil público
            </caption>
            <thead>
              <tr>
                <th class="adm-num">#</th>
                <th>Artista</th>
                <th>Categoria</th>
                <th>Cidade</th>
                <th>Avaliação</th>
                <th class="adm-num">Avaliações</th>
                <th class="adm-num">Favoritos</th>
                <th class="adm-num">Valor/hora</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ranking as $artista): ?>
                <?php
                $medalha = '';
                if ($artista['posicao'] <= 3) {
                    $medalha = ' adm-posicao-' . (int) $artista['posicao'];
                }
                ?>
                <tr>
                  <td class="adm-num">
                    <span class="adm-posicao<?php echo $medalha; ?>"><?php echo (int) $artista['posicao']; ?></span>
                  </td>
                  <td class="principal">
                    <a href="PerfilFreelancer.php?id=<?php echo (int) $artista['id_freelancer']; ?>">
                      <?php echo htmlspecialchars($artista['nome']); ?>
                    </a>
                    <span class="secundario"><?php echo htmlspecialchars($artista['profissao']); ?></span>
                  </td>
                  <td data-label="Categoria"><?php echo htmlspecialchars($artista['categoria']); ?></td>
                  <td data-label="Cidade"><?php echo htmlspecialchars($artista['cidade']); ?>/<?php echo htmlspecialchars($artista['estado']); ?></td>
                  <td data-label="Avaliação"><?php echo admin_render_estrelas($artista['media']); ?></td>
                  <td class="adm-num" data-label="Avaliações"><?php echo (int) $artista['avaliacoes']; ?></td>
                  <td class="adm-num" data-label="Favoritos"><?php echo (int) $artista['favoritos']; ?></td>
                  <td class="adm-num" data-label="Valor/hora"><?php echo formatar_valor_freelancer($artista['valor_hora']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

      <?php endif; ?>

      <div class="adm-secao-topo adm-secao-topo-apertado">
        <h2>Interação da rede</h2>
      </div>

      <ul class="adm-metricas">
        <li><span>Avaliações</span><b><?php echo (int) $social['avaliacoes']; ?></b></li>
        <li><span>Favoritos</span><b><?php echo (int) $social['favoritos']; ?></b></li>
        <li><span>Mensagens</span><b><?php echo (int) $social['mensagens']; ?></b></li>
        <li><span>Portfólios</span><b><?php echo (int) $social['portfolios']; ?></b></li>
        <li><span>Notificações abertas</span><b><?php echo (int) $social['notificacoes_abertas']; ?></b></li>
      </ul>
    </section>

  </div>
</main>

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>
