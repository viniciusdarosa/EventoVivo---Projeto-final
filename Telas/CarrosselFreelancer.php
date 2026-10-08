<?php
/* ==========================================================
 * COMPONENTE: CarrosselFreelancer.php
 * Painel do artista para gerenciar as fotos do próprio carrossel
 * de trabalho: adicionar (até CARROSSEL_MAX_FOTOS) e excluir.
 * ========================================================== */

/**
 * CarrosselFreelancer.php
 *
 * Somente o dono do perfil pode gerenciar as fotos.
 * O freelancer é obtido EXCLUSIVAMENTE a partir da sessão
 * ($_SESSION['id_usuario']) — nenhum input do usuário serve
 * para escolher de quem é o carrossel.
 *
 * Ações (POST):
 *   - acao=adicionar : valida upload, respeita o limite, grava arquivo e linha
 *   - acao=excluir   : remove a linha (WHERE inclui freelancer_id) e o arquivo
 */
session_start();
require_once dirname(__FILE__) . '/../config/conexao.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_freelancers.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuarioId = (int) $_SESSION['id_usuario'];

$freelancer = buscar_freelancer_por_usuario($conexao, $usuarioId);

if (!$freelancer) {
    header('Location: CadastrarFreelancer.php');
    exit;
}

$idFreelancer = (int) $freelancer['id_freelancer'];
$erros = array();
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao = isset($_POST['acao']) ? $_POST['acao'] : '';

    if ($acao === 'adicionar') {

        $uploadInfo = validar_upload_imagem_freelancer(isset($_FILES['foto']) ? $_FILES['foto'] : null);

        if (!$uploadInfo['enviado']) {
            $erros[] = 'Selecione uma imagem para adicionar ao carrossel.';
        } elseif (!$uploadInfo['ok']) {
            $erros[] = $uploadInfo['erro'];
        }

        if (empty($erros)) {
            $total = contar_carrossel_freelancer($conexao, $idFreelancer);

            if ($total >= CARROSSEL_MAX_FOTOS) {
                $erros[] = 'O carrossel já possui o máximo de ' . CARROSSEL_MAX_FOTOS . ' fotos. Exclua uma foto para adicionar outra.';
            }
        }

        if (empty($erros)) {
            $legenda = isset($_POST['legenda']) ? trim($_POST['legenda']) : '';

            if (strlen($legenda) > 255) {
                $erros[] = 'A legenda deve ter no máximo 255 caracteres.';
            } elseif ($legenda === '') {
                $legenda = null;
            }
        }

        if (empty($erros)) {
            $novoNomeImagem = salvar_upload_imagem_freelancer($_FILES['foto'], $uploadInfo['extensao']);

            if ($novoNomeImagem === false) {
                $erros[] = 'Não foi possível salvar a imagem. Tente novamente.';
            } else {
                $resultado = inserir_foto_carrossel($conexao, $idFreelancer, $novoNomeImagem, $legenda);

                if ($resultado['ok']) {
                    $sucesso = 'Foto adicionada ao carrossel.';
                } else {
                    excluir_imagem_freelancer($novoNomeImagem);
                    $erros[] = $resultado['erro'];
                }
            }
        }

    } elseif ($acao === 'excluir') {

        $idFoto = isset($_POST['id_foto']) && ctype_digit($_POST['id_foto']) ? (int) $_POST['id_foto'] : 0;

        if ($idFoto <= 0) {
            $erros[] = 'Foto inválida.';
        } else {
            $resultado = excluir_foto_carrossel($conexao, $idFoto, $idFreelancer);

            if ($resultado['ok']) {
                excluir_imagem_freelancer($resultado['imagem']);
                $sucesso = 'Foto excluída do carrossel.';
            } else {
                $erros[] = $resultado['erro'];
            }
        }

    } else {
        $erros[] = 'Ação inválida.';
    }
}

$fotos = buscar_carrossel_freelancer($conexao, $idFreelancer);
$totalFotos = count($fotos);
$carrosselCheio = $totalFotos >= CARROSSEL_MAX_FOTOS;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventoVivo — Fotos do Trabalho</title>
  <meta name="theme-color" content="#110f0c">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Courier+Prime:wght@400;700&family=Permanent+Marker&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../Css/crud_eventos.css">
  <link rel="stylesheet" href="../Css/style.css">
  <style>
    .carrossel-contagem {
      color: var(--paper-dim);
      font-size: .85rem;
      margin: .75rem 0 1.5rem;
      text-transform: uppercase;
      letter-spacing: .05em;
    }
    .carrossel-contagem strong { color: var(--paper); }

    .carrossel-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2.5rem;
    }
    .carrossel-card {
      background: var(--steel);
      border: 2px solid var(--rule);
      box-shadow: var(--shadow-hard);
      overflow: hidden;
      transition: transform var(--ease), border-color var(--ease);
    }
    .carrossel-card:hover {
      transform: translateY(-2px);
      border-color: var(--accent);
    }
    .carrossel-card-img {
      display: block;
      width: 100%;
      aspect-ratio: 4/3;
      object-fit: cover;
      background: var(--ink-2);
    }
    .carrossel-card-corpo { padding: 1rem; }
    .carrossel-card-legenda {
      color: var(--paper-dim);
      font-size: .8rem;
      line-height: 1.5;
      margin-bottom: .75rem;
      min-height: 2.4em;
    }
    .carrossel-card form { margin: 0; }
    .btn-excluir-foto {
      display: block;
      width: 100%;
      background: transparent;
      border: 2px solid var(--rule);
      color: var(--paper-dim);
      font-family: var(--font-body);
      font-size: .75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .05em;
      padding: .5rem;
      cursor: pointer;
      transition: border-color var(--ease), color var(--ease), background var(--ease);
    }
    .btn-excluir-foto:hover {
      border-color: var(--blood);
      color: var(--blood);
      background: rgba(179, 18, 30, .1);
    }

    .carrossel-vazio {
      background: var(--steel);
      border: 2px dashed var(--rule);
      padding: 2.5rem 1.5rem;
      text-align: center;
      color: var(--paper-dim);
      margin-bottom: 2.5rem;
    }

    .carrossel-form {
      background: var(--steel);
      border: 2px solid var(--rule);
      box-shadow: var(--shadow-hard);
      padding: 2rem;
      max-width: 640px;
    }
    .carrossel-form .campo { margin-bottom: 1rem; }
    .carrossel-form label {
      display: block;
      margin-bottom: .35rem;
      font-weight: 700;
      text-transform: uppercase;
      font-size: .75rem;
      color: var(--paper-dim);
      letter-spacing: .05em;
    }
    .carrossel-form input[type="file"] {
      display: block;
      width: 100%;
      max-width: 100%;
      min-width: 0;
      box-sizing: border-box;
      color: var(--paper);
      font-family: var(--font-body);
      font-size: .9rem;
    }
    .carrossel-form input[type="text"] {
      width: 100%;
      max-width: 100%;
      min-width: 0;
      box-sizing: border-box;
      background: var(--ink);
      border: 2px solid var(--rule);
      color: var(--paper);
      padding: .75rem;
      font-family: var(--font-body);
      font-size: .9rem;
    }
    .carrossel-form input[type="text"]:focus { outline: none; border-color: var(--accent); }
    .carrossel-form .ajuda {
      color: var(--paper-dim);
      font-size: .75rem;
      margin-top: .35rem;
    }
    .carrossel-form fieldset {
      border: 1px solid var(--rule);
      padding: 1rem;
      margin: 0 0 1.5rem;
      min-width: 0;
    }
    .carrossel-form legend {
      color: var(--paper-dim);
      font-size: .7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .08em;
      padding: 0 .5rem;
    }
    .carrossel-acoes { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; }
    /* .btn-primary usa white-space: nowrap; aqui ele precisa quebrar. */
    .carrossel-acoes .btn,
    .carrossel-acoes .btn-icone { min-width: 0; max-width: 100%; box-sizing: border-box; }
    .carrossel-acoes .btn-primary { white-space: normal; text-align: center; }

    @media (max-width: 720px) {
      .carrossel-form { padding: 1.5rem; }
      .carrossel-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
    }
  </style>
</head>
<?php require dirname(__FILE__) . '/Componentes/header.php'; ?>
<body>

<main class="admin-page freelancer-form-page">
  <div class="wrap">

    <p class="eyebrow">PAINEL DE CONTROLE</p>
    <h1>FOTOS DO TRABALHO</h1>

    <p class="carrossel-contagem">
      <strong><?php echo $totalFotos; ?></strong> de <strong><?php echo CARROSSEL_MAX_FOTOS; ?></strong> fotos no carrossel
    </p>

    <?php if ($sucesso !== ''): ?>
      <div class="alert alert-sucesso">
        <?php echo htmlspecialchars($sucesso); ?>
        <a href="PerfilFreelancer.php?id=<?php echo $idFreelancer; ?>">Ver meu perfil público</a>
      </div>
    <?php endif; ?>

    <?php if (!empty($erros)): ?>
      <div class="alert alert-erro">
        <strong>Corrija os itens abaixo:</strong>
        <ul>
          <?php foreach ($erros as $erro): ?>
            <li><?php echo htmlspecialchars($erro); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if (empty($fotos)): ?>
      <div class="carrossel-vazio">
        Seu carrossel está vazio. Adicione até <?php echo CARROSSEL_MAX_FOTOS; ?> fotos do seu trabalho abaixo.
      </div>
    <?php else: ?>
      <div class="carrossel-grid">
        <?php foreach ($fotos as $foto): ?>
          <article class="carrossel-card">
            <img class="carrossel-card-img" src="<?php echo htmlspecialchars(freelancer_portfolio_src($foto['imagem'])); ?>" alt="<?php echo htmlspecialchars($foto['legenda'] !== null && $foto['legenda'] !== '' ? $foto['legenda'] : 'Foto do trabalho'); ?>">
            <div class="carrossel-card-corpo">
              <p class="carrossel-card-legenda">
                <?php echo ($foto['legenda'] !== null && $foto['legenda'] !== '') ? htmlspecialchars($foto['legenda']) : '<em>Sem legenda</em>'; ?>
              </p>
              <form action="CarrosselFreelancer.php" method="post"
                    onsubmit="return confirm('Remover esta foto do carrossel?');">
                <input type="hidden" name="acao" value="excluir">
                <input type="hidden" name="id_foto" value="<?php echo (int) $foto['id_foto']; ?>">
                <button type="submit" class="btn-excluir-foto">Remover</button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <h2 style="font-family:var(--font-display);text-transform:uppercase;font-size:1.4rem;border-bottom:3px solid var(--accent);padding-bottom:.4rem;margin-bottom:1.5rem;display:inline-block;">Adicionar foto</h2>

    <?php if ($carrosselCheio): ?>
      <div class="alert alert-erro">
        Você já atingiu o limite de <?php echo CARROSSEL_MAX_FOTOS; ?> fotos.
        Exclua uma foto acima para adicionar outra.
      </div>
    <?php else: ?>
      <form class="carrossel-form" action="CarrosselFreelancer.php" method="post" enctype="multipart/form-data">

        <input type="hidden" name="acao" value="adicionar">

        <fieldset>
          <legend>Imagem (obrigatória)</legend>
          <div class="campo">
            <label for="foto">Selecione a foto do seu trabalho</label>
            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/gif" required>
            <p class="ajuda">Formatos aceitos: JPG, PNG ou GIF. Tamanho máximo: 2MB.</p>
          </div>
        </fieldset>

        <fieldset>
          <legend>Legenda (opcional)</legend>
          <div class="campo">
            <label for="legenda">Texto exibido junto com a foto</label>
            <input type="text" id="legenda" name="legenda" maxlength="255"
                   placeholder="Ex: Show no Festival de Verão 2026">
            <p class="ajuda">Até 255 caracteres.</p>
          </div>
        </fieldset>

        <div class="carrossel-acoes">
          <button type="submit" class="btn btn-primary">ADICIONAR AO CARROSSEL</button>
          <a href="PerfilFreelancer.php?id=<?php echo $idFreelancer; ?>" class="btn-icone">Ver perfil público</a>
          <a href="CRUD_Freelancers.php" class="btn-icone">Voltar</a>
        </div>

      </form>
    <?php endif; ?>

  </div>
</main>

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>
</body>
</html>
