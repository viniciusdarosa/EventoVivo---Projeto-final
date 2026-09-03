<?php
/**
 * CadastrarFreelancer.php
 *
 * Formulário de cadastro de perfil de freelancer + processamento do
 * INSERT (quando o formulário é enviado via POST), incluindo o
 * upload da foto de capa/portfolio.
 *
 * id_freelancer e usuario_id NÃO vêm do formulário:
 * são preenchidos automaticamente pelo servidor.
 */
session_start();
require_once dirname(__FILE__) . '/../config/conexao.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_freelancers.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_eventos.php'; // Para hash_senha se necessário

// Só usuários logados podem cadastrar perfil de freelancer
if (!isset($_SESSION['id_usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuarioId = (int) $_SESSION['id_usuario'];

// Verifica se já tem perfil de freelancer
$freelancerExistente = buscar_freelancer_por_usuario($conexao, $usuarioId);
if ($freelancerExistente) {
    header('Location: EditarFreelancer.php');
    exit;
}

$erros = array();
$sucesso = false;

$dados = array(
    'categoria_id'  => '',
    'profissao'     => '',
    'descricao'     => '',
    'experiencia'   => '',
    'valor_hora'    => '',
    'rede_social'   => '',
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($dados as $campo => $valorPadrao) {
        if (isset($_POST[$campo])) {
            $dados[$campo] = trim($_POST[$campo]);
        }
    }

    // ---- Validação server-side ----
    if ($dados['categoria_id'] === '' || !ctype_digit($dados['categoria_id'])) {
        $erros[] = 'Selecione uma categoria de serviço.';
    }
    if ($dados['profissao'] === '') {
        $erros[] = 'Informe sua profissão/título (ex: Músico, Fotógrafo, Designer).';
    }
    if ($dados['descricao'] === '') {
        $erros[] = 'Informe uma descrição dos seus serviços.';
    }
    if ($dados['valor_hora'] !== '' && (!is_numeric($dados['valor_hora']) || (float) $dados['valor_hora'] < 0)) {
        $erros[] = 'Informe um valor válido para hora (use 0 para "A combinar").';
    }

    // Upload de imagem de capa (opcional)
    $uploadInfo = validar_upload_imagem_freelancer(isset($_FILES['foto_capa']) ? $_FILES['foto_capa'] : null);

    if ($uploadInfo['enviado'] && !$uploadInfo['ok']) {
        $erros[] = $uploadInfo['erro'];
    }

    if (empty($erros)) {
        $nomeImagem = null;
        if ($uploadInfo['enviado']) {
            $nomeImagem = salvar_upload_imagem_freelancer($_FILES['foto_capa'], $uploadInfo['extensao']);
            if ($nomeImagem === false) {
                $erros[] = 'Não foi possível salvar a imagem enviada. Tente novamente.';
            }
        }

        if (empty($erros)) {
            $sql = "INSERT INTO freelancers
                        (usuario_id, categoria_id, profissao, descricao,
                         experiencia, valor_hora, portfolio, rede_social)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conexao->prepare($sql);

            if ($stmt === false) {
                $erros[] = 'Erro ao preparar a operação: ' . $conexao->error;
            } else {
                $categoriaId = (int) $dados['categoria_id'];
                $valorHora = $dados['valor_hora'] !== '' ? (float) $dados['valor_hora'] : null;

                $stmt->bind_param(
                    'iisssdss',
                    $usuarioId,
                    $categoriaId,
                    $dados['profissao'],
                    $dados['descricao'],
                    $dados['experiencia'],
                    $valorHora,
                    $nomeImagem,
                    $dados['rede_social']
                );

                if ($stmt->execute()) {
                    $sucesso = true;
                    foreach ($dados as $campo => $valorPadrao) {
                        $dados[$campo] = '';
                    }
                } else {
                    if ($nomeImagem) {
                        excluir_imagem_freelancer($nomeImagem);
                    }
                    $erros[] = 'Erro ao salvar o perfil: ' . $stmt->error;
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
  <title>EventoVivo — Criar Perfil de Artista</title>
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
    <h1>CRIAR PERFIL DE ARTISTA</h1>

    <?php if ($sucesso): ?>
      <div class="alert alert-sucesso">
        Perfil de artista criado com sucesso!
        <a href="PerfilFreelancer.php?id=<?php echo (int) $conexao->insert_id; ?>">Ver meu perfil público</a>
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

    <form class="freelancer-form" action="CadastrarFreelancer.php" method="post" enctype="multipart/form-data">

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
        <label for="foto_capa">Foto de Capa do Perfil (JPG, PNG ou GIF, até 2MB) — opcional</label>
        <input type="file" id="foto_capa" name="foto_capa" accept="image/jpeg,image/png,image/gif">
      </div>

      <div class="form-acoes">
        <button type="submit" class="btn btn-primary">CRIAR PERFIL</button>
        <a href="CRUD_Freelancers.php" class="btn-icone">Cancelar</a>
      </div>

    </form>

  </div>
</main>

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>
</body>
</html>