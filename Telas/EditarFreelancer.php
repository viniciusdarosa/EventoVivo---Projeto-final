<?php
/* ==========================================================
 * COMPONENTE: EditarFreelancer.php
 * Carrega o perfil de freelancer existente e processa sua atualização, incluindo a troca opcional da foto de perfil.
 * ========================================================== */

/**
 * EditarFreelancer.php
 *
 * Carrega um perfil de freelancer existente e exibe o formulário
 * pré-preenchido. No POST, processa o UPDATE. O upload de nova
 * foto de perfil é opcional: se o usuário não enviar arquivo novo,
 * a foto atual de usuario.foto_perfil é mantida; se enviar, a foto
 * antiga é apagada do servidor.
 */
require_once dirname(__FILE__) . '/../config/bootstrap.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_freelancers.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuarioId = (int) $_SESSION['id_usuario'];

// Busca o freelancer do usuário logado
$freelancer = buscar_freelancer_por_usuario($conexao, $usuarioId);

if (!$freelancer) {
    header('Location: CadastrarFreelancer.php');
    exit;
}

$idFreelancer = (int) $freelancer['id_freelancer'];
$imagemAtual = isset($freelancer['foto_perfil']) ? $freelancer['foto_perfil'] : null;

$erros = array();
$sucesso = false;

$dados = array(
    'categoria_id'  => (string) $freelancer['categoria_id'],
    'profissao'     => $freelancer['profissao'],
    'descricao'     => $freelancer['descricao'],
    'experiencia'   => $freelancer['experiencia'],
    'valor_hora'    => $freelancer['valor_hora'],
    'rede_social'   => $freelancer['rede_social'],
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($dados as $campo => $valorPadrao) {
        if (isset($_POST[$campo])) {
            $dados[$campo] = trim($_POST[$campo]);
        }
    }

    // ---- Validação ----
    if ($dados['categoria_id'] === '' || !ctype_digit($dados['categoria_id'])) {
        $erros[] = 'Selecione uma categoria de serviço.';
    }
    if ($dados['profissao'] === '') {
        $erros[] = 'Informe sua profissão/título.';
    }
    if ($dados['descricao'] === '') {
        $erros[] = 'Informe uma descrição dos seus serviços.';
    }
    if ($dados['valor_hora'] !== '' && (!is_numeric($dados['valor_hora']) || (float) $dados['valor_hora'] < 0)) {
        $erros[] = 'Informe um valor válido para hora (use 0 para "A combinar").';
    }

    // ---- Upload de nova foto de perfil é OPCIONAL na edição ----
    $uploadInfo = validar_upload_imagem_freelancer(isset($_FILES['foto_perfil']) ? $_FILES['foto_perfil'] : null);

    if ($uploadInfo['enviado'] && !$uploadInfo['ok']) {
        $erros[] = $uploadInfo['erro'];
    }

    if (empty($erros)) {
        $nomeFotoEnviada = null;

        if ($uploadInfo['enviado']) {
            $nomeFotoEnviada = salvar_upload_foto_perfil($_FILES['foto_perfil'], $uploadInfo['extensao']);

            if ($nomeFotoEnviada === false) {
                $erros[] = 'Não foi possível salvar a nova imagem. Tente novamente.';
            }
        }

        if (empty($erros)) {
            $sql = "UPDATE freelancers SET
                        categoria_id = ?, profissao = ?, descricao = ?,
                        experiencia = ?, valor_hora = ?, rede_social = ?
                    WHERE id_freelancer = ? AND usuario_id = ?";

            $stmt = $conexao->prepare($sql);

            if ($stmt === false) {
                $erros[] = 'Erro ao preparar a operação: ' . $conexao->error;
                if ($nomeFotoEnviada) {
                    excluir_foto_perfil($nomeFotoEnviada);
                }
            } else {
                $categoriaId = (int) $dados['categoria_id'];
                $valorHora = $dados['valor_hora'] !== '' ? (float) $dados['valor_hora'] : null;

                $stmt->bind_param(
                    'isssdsii',
                    $categoriaId,
                    $dados['profissao'],
                    $dados['descricao'],
                    $dados['experiencia'],
                    $valorHora,
                    $dados['rede_social'],
                    $idFreelancer,
                    $usuarioId
                );

                if ($stmt->execute()) {
                    $sucesso = true;

                    if ($nomeFotoEnviada) {
                        if (atualizar_foto_perfil_usuario($conexao, $usuarioId, $nomeFotoEnviada)) {
                            $imagemAtual = $nomeFotoEnviada;
                        } else {
                            excluir_foto_perfil($nomeFotoEnviada);
                            $erros[] = 'O perfil foi atualizado, mas não foi possível gravar a nova foto de perfil.';
                        }
                    }
                } else {
                    if ($nomeFotoEnviada) {
                        excluir_foto_perfil($nomeFotoEnviada);
                    }
                    $erros[] = 'Erro ao atualizar o perfil: ' . $stmt->error;
                }

                $stmt->close();
            }
        }
    }
}

$categorias = buscar_categorias_servicos($conexao);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventoVivo — Editar Perfil de Artista</title>
  <meta name="theme-color" content="#110f0c">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Courier+Prime:wght@400;700&family=Permanent+Marker&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../Css/crud_eventos.css">
  <link rel="stylesheet" href="../Css/style.css">
</head>
<?php require dirname(__FILE__) . '/Componentes/header.php'; ?>
<body>

<main class="admin-page freelancer-form-page">
  <div class="wrap">

    <p class="eyebrow">PAINEL DE CONTROLE</p>
    <h1>EDITAR PERFIL DE ARTISTA</h1>

    <?php if ($sucesso): ?>
      <div class="alert alert-sucesso">
        Perfil atualizado com sucesso!
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

    <form class="freelancer-form" action="EditarFreelancer.php" method="post" enctype="multipart/form-data">

      <input type="hidden" name="id_freelancer" value="<?php echo $idFreelancer; ?>">

      <div class="campo campo-full">
        <label for="categoria_id">Categoria de Serviço</label>
        <select id="categoria_id" name="categoria_id" required>
          <option value="">Selecione...</option>
          <?php foreach ($categorias as $categoria): ?>
            <option value="<?php echo (int) $categoria['id_categoria']; ?>"
              <?php echo ((string) $categoria['id_categoria'] === $dados['categoria_id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($categoria['nome']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="campo campo-full">
        <label for="profissao">Profissão / Título</label>
        <input type="text" id="profissao" name="profissao" maxlength="100" required
               value="<?php echo htmlspecialchars($dados['profissao']); ?>"
               placeholder="Ex: Músico, Fotógrafo, Designer Gráfico, Dançarino...">
      </div>

      <div class="campo campo-full">
        <label for="descricao">Descrição dos Serviços</label>
        <textarea id="descricao" name="descricao" required><?php echo htmlspecialchars($dados['descricao']); ?></textarea>
      </div>

      <div class="campo campo-full">
        <label for="experiencia">Experiência</label>
        <textarea id="experiencia" name="experiencia"><?php echo htmlspecialchars($dados['experiencia']); ?></textarea>
      </div>

      <div class="campo">
        <label for="valor_hora">Valor por Hora (R$) — opcional</label>
        <input type="number" id="valor_hora" name="valor_hora" min="0" step="0.01"
               value="<?php echo htmlspecialchars($dados['valor_hora']); ?>"
               placeholder="0 para 'A combinar'">
      </div>

      <div class="campo campo-full">
        <label for="rede_social">Instagram / Rede Social — opcional</label>
        <input type="text" id="rede_social" name="rede_social" maxlength="255"
               value="<?php echo htmlspecialchars($dados['rede_social']); ?>"
               placeholder="@seuusuario (apenas o usuário, sem @)">
      </div>

      <div class="campo campo-full">
        <label for="foto_perfil">Foto de Perfil (deixe em branco para manter a atual)</label>

        <div class="imagem-atual">
          <?php
          $srcFotoAtual = !empty($imagemAtual) ? freelancer_foto_src($imagemAtual) : '';
          if ($srcFotoAtual !== ''):
          ?>
            <img src="<?php echo htmlspecialchars($srcFotoAtual); ?>" alt="Foto de perfil atual">
          <?php else: ?>
            <div class="sem-imagem">Sem foto de perfil</div>
          <?php endif; ?>
          <input type="file" id="foto_perfil" name="foto_perfil" accept="image/jpeg,image/png,image/gif">
        </div>
      </div>

      <div class="form-acoes">
        <button type="submit" class="btn btn-primary">SALVAR ALTERAÇÕES</button>
        <a href="CRUD_Freelancers.php" class="btn-icone">Cancelar</a>
      </div>

    </form>

  </div>
</main>

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>
</body>
</html>