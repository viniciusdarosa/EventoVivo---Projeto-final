<?php
/**
 * CRUD_Freelancers.php
 *
 * Lista os perfis de freelancer cadastrados pelo usuário logado
 * (um usuário pode ter apenas um perfil), com botões de Editar e Excluir.
 */
session_start();
require_once dirname(__FILE__) . '/../config/conexao.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_freelancers.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuarioId = (int) $_SESSION['id_usuario'];

// Busca o freelancer do usuário logado
$freelancer = buscar_freelancer_por_usuario($conexao, $usuarioId);
$temPerfil = $freelancer !== false;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventoVivo — Gerenciar Perfil de Artista</title>
  <meta name="theme-color" content="#110f0c">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Courier+Prime:wght@400;700&family=Permanent+Marker&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../Css/crud_eventos.css">
  <link rel="stylesheet" href="../Css/style.css">
</head>
<?php require dirname(__FILE__) . '/Componentes/header.php'; ?>
<body>

<main class="admin-page">
  <section class="page-heading">
    <div class="wrap heading-inner">
      <div>
        <p class="eyebrow">PAINEL DE CONTROLE</p>
        <h1>MEU PERFIL DE ARTISTA</h1>
        <p class="heading-sub">
          Gerencie seu perfil de freelancer/artista no EventoVivo.
        </p>
      </div>

      <?php if ($temPerfil): ?>
        <a class="btn btn-primary" href="PerfilFreelancer.php?id=<?php echo (int) $freelancer['id_freelancer']; ?>" target="_blank">
          Ver Perfil Público
        </a>
      <?php else: ?>
        <a class="btn btn-primary" href="CadastrarFreelancer.php">
          + CRIAR PERFIL
        </a>
      <?php endif; ?>
    </div>
  </section>

  <section class="events-section">
    <div class="wrap">

      <div class="list-header">
        <div>
          <h2><?php echo $temPerfil ? 'PERFIL CADASTRADO' : 'NENHUM PERFIL ENCONTRADO'; ?></h2>
          <span class="counter"><?php echo $temPerfil ? '1' : '0'; ?> perfil</span>
        </div>
      </div>

      <?php if (!$temPerfil): ?>

        <div class="estado-vazio">
          <p>Você ainda não criou seu perfil de artista.</p>
          <a href="CadastrarFreelancer.php" class="btn btn-primary">Criar meu perfil</a>
        </div>

      <?php else: ?>

        <div class="eventos-grid">
          <article class="evento-card freelancer-card">

            <?php if (!empty($freelancer['portfolio'])): ?>
              <img class="evento-card-imagem"
                   src="<?php echo htmlspecialchars(freelancer_portfolio_src($freelancer['portfolio'])); ?>"
                   alt="Capa do perfil de <?php echo htmlspecialchars($_SESSION['nome_usuario']); ?>">
            <?php else: ?>
              <div class="evento-card-imagem placeholder-cover" style="display:flex;align-items:center;justify-content:center;background:var(--steel);">
                <span style="font-family:var(--font-display);font-size:3rem;color:var(--paper-dim);">🎨</span>
              </div>
            <?php endif; ?>

            <div class="evento-card-corpo">
              <p class="evento-card-categoria"><?php echo htmlspecialchars($freelancer['categoria_nome']); ?></p>
              <h3 class="evento-card-titulo"><?php echo htmlspecialchars($freelancer['profissao']); ?></h3>

              <p class="evento-card-info">
                <?php echo htmlspecialchars($freelancer['descricao']); ?>
              </p>

              <?php if (!empty($freelancer['valor_hora']) && $freelancer['valor_hora'] > 0): ?>
                <p class="evento-card-info">
                  <strong><?php echo formatar_valor_freelancer($freelancer['valor_hora']); ?></strong>
                </p>
              <?php endif; ?>

              <div class="evento-card-rodape">
                <span class="evento-card-valor">
                  <?php echo htmlspecialchars($_SESSION['nome_usuario']); ?>
                </span>

                <div class="evento-card-acoes">
                  <a class="btn-icone" href="EditarFreelancer.php">Editar</a>

                  <form method="post" action="ExcluirFreelancer.php"
                        onsubmit="return confirm('Tem certeza que deseja excluir seu perfil de artista? Essa ação não pode ser desfeita.');"
                        style="display:inline;">
                    <input type="hidden" name="id_freelancer" value="<?php echo (int) $freelancer['id_freelancer']; ?>">
                    <button type="submit" class="btn-icone btn-icone-excluir">Excluir</button>
                  </form>

                  <a class="btn-icone" href="PerfilFreelancer.php?id=<?php echo (int) $freelancer['id_freelancer']; ?>" target="_blank">Ver Público</a>
                </div>
              </div>
            </div>
          </article>
        </div>

      <?php endif; ?>

    </div>
  </section>
</main>

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>
</body>
</html>