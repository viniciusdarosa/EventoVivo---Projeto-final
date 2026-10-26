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
require_once dirname(__FILE__) . '/../config/bootstrap.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_freelancers.php';

exigir_login('Login.php');
$usuarioId = usuario_id();

$freelancer = buscar_freelancer_por_usuario($conexao, $usuarioId);

if (!$freelancer) {
    redirecionar('CadastrarFreelancer.php');
}

$idFreelancer = (int) $freelancer['id_freelancer'];
$erros = array();
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_exigir();

    $acao = entrada_texto($_POST, 'acao');

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
            $legenda = entrada_texto($_POST, 'legenda');

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

        $idFoto = entrada_inteiro($_POST, 'id_foto');

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

$pageTitle = 'EventoVivo — Fotos do Trabalho';
$extraCss = array('../Css/crud_eventos.css');

require dirname(__FILE__) . '/Componentes/header.php';
?>

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
            <img class="carrossel-card-img" src="<?php echo htmlspecialchars(freelancer_foto_trabalho_src($foto['imagem'])); ?>" alt="<?php echo htmlspecialchars($foto['legenda'] !== null && $foto['legenda'] !== '' ? $foto['legenda'] : 'Foto do trabalho'); ?>">
            <div class="carrossel-card-corpo">
              <p class="carrossel-card-legenda">
                <?php echo ($foto['legenda'] !== null && $foto['legenda'] !== '') ? htmlspecialchars($foto['legenda']) : '<em>Sem legenda</em>'; ?>
              </p>
              <form action="CarrosselFreelancer.php" method="post"
                    onsubmit="return confirm('Remover esta foto do carrossel?');">
                <?php echo csrf_campo(); ?>
                <input type="hidden" name="acao" value="excluir">
                <input type="hidden" name="id_foto" value="<?php echo (int) $foto['id_foto']; ?>">
                <button type="submit" class="btn-excluir-foto">Remover</button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <h2 class="section-title">Adicionar foto</h2>

    <?php if ($carrosselCheio): ?>
      <div class="alert alert-erro">
        Você já atingiu o limite de <?php echo CARROSSEL_MAX_FOTOS; ?> fotos.
        Exclua uma foto acima para adicionar outra.
      </div>
    <?php else: ?>
      <form class="carrossel-form" action="CarrosselFreelancer.php" method="post" enctype="multipart/form-data" data-loading="true">
        <?php echo csrf_campo(); ?>

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
