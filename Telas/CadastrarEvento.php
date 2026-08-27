<?php
/**
 * CadastrarEvento.php
 *
 * Formulário de cadastro de um novo evento + processamento do
 * INSERT (quando o formulário é enviado via POST), incluindo o
 * upload da imagem de capa.
 *
 * id_evento, data_publicacao e usuario_id NÃO vêm do formulário:
 * são preenchidos automaticamente pelo servidor.
 */

session_start();
require dirname(__FILE__) . '../config/conexao.php';
require dirname(__FILE__) . '/Componentes/funcoes_eventos.php';

// Só usuários logados podem cadastrar evento. O id do organizador
// vem da sessão, nunca de um campo do formulário (evita que alguém
// cadastre um evento em nome de outro usuário).
if (!isset($_SESSION['id_usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuarioId = (int) $_SESSION['id_usuario'];

$erros = array();
$sucesso = false;

// Valores enviados pelo usuário, guardados aqui para repopular o
// formulário caso alguma validação falhe.
$dados = array(
    'categoria_id'        => '',
    'titulo'               => '',
    'descricao'            => '',
    'data_inicio_evento'   => '',
    'data_fim_evento'      => '',
    'hora_inicio'          => '',
    'hora_fim'             => '',
    'endereco'             => '',
    'numero'               => '',
    'cidade'               => '',
    'estado'               => '',
    'cep'                  => '',
    'valor'                => '',
    'vagas'                => '',
    'faixa_etaria'         => 'Livre',
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recupera e faz um trim() básico em cada campo de texto.
    foreach ($dados as $campo => $valorPadrao) {
        if (isset($_POST[$campo])) {
            $dados[$campo] = trim($_POST[$campo]);
        }
    }

    // ---- Validação server-side dos campos obrigatórios ----
    if ($dados['categoria_id'] === '' || !ctype_digit($dados['categoria_id'])) {
        $erros[] = 'Selecione uma categoria.';
    }
    if ($dados['titulo'] === '') {
        $erros[] = 'Informe o título do evento.';
    }
    if ($dados['data_inicio_evento'] === '') {
        $erros[] = 'Informe a data de início.';
    }
    if ($dados['data_fim_evento'] === '') {
        $erros[] = 'Informe a data de término.';
    }
    if ($dados['data_inicio_evento'] !== '' && $dados['data_fim_evento'] !== ''
        && $dados['data_fim_evento'] < $dados['data_inicio_evento']) {
        $erros[] = 'A data de término não pode ser anterior à data de início.';
    }
    if ($dados['hora_inicio'] === '') {
        $erros[] = 'Informe o horário de início.';
    }
    if ($dados['hora_fim'] === '') {
        $erros[] = 'Informe o horário de término.';
    }
    if ($dados['endereco'] === '') {
        $erros[] = 'Informe o endereço.';
    }
    if ($dados['cidade'] === '') {
        $erros[] = 'Informe a cidade.';
    }
    if ($dados['estado'] === '' || strlen($dados['estado']) !== 2) {
        $erros[] = 'Informe o estado (sigla com 2 letras, ex: SC).';
    }
    if ($dados['valor'] === '' || !is_numeric($dados['valor']) || (float) $dados['valor'] < 0) {
        $erros[] = 'Informe um valor válido (use 0 para evento gratuito).';
    }
    if ($dados['vagas'] === '' || !ctype_digit((string) $dados['vagas'])) {
        $erros[] = 'Informe a quantidade de vagas.';
    }

    $faixasValidas = array('Livre', '10', '12', '14', '16', '18+');
    if (!in_array($dados['faixa_etaria'], $faixasValidas)) {
        $dados['faixa_etaria'] = 'Livre';
    }

    // ---- Validação da imagem de capa (opcional no cadastro? Aqui
    // tratamos como obrigatória, já que é a "capa" do evento —
    // ajuste para opcional se preferir) ----
    $uploadInfo = validar_upload_imagem_evento(isset($_FILES['imagem_capa']) ? $_FILES['imagem_capa'] : null);

    if (!$uploadInfo['enviado']) {
        $erros[] = 'Envie uma imagem de capa para o evento.';
    } elseif (!$uploadInfo['ok']) {
        $erros[] = $uploadInfo['erro'];
    }

    // ---- Se passou em tudo, salva a imagem e insere no banco ----
    if (empty($erros)) {
        $nomeImagem = salvar_upload_imagem_evento($_FILES['imagem_capa'], $uploadInfo['extensao']);

        if ($nomeImagem === false) {
            $erros[] = 'Não foi possível salvar a imagem enviada. Tente novamente.';
        } else {
            $sql = "INSERT INTO eventos
                        (usuario_id, categoria_id, titulo, descricao,
                         data_inicio_evento, data_fim_evento, hora_inicio, hora_fim,
                         endereco, numero, cidade, estado, cep,
                         valor, vagas, imagem_capa, faixa_etaria, data_publicacao)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $conexao->prepare($sql);

            if ($stmt === false) {
                $erros[] = 'Erro ao preparar a operação: ' . $conexao->error;
            } else {
                $categoriaId = (int) $dados['categoria_id'];
                $vagas = (int) $dados['vagas'];
                $valor = (float) $dados['valor'];

                // Tipos: i=inteiro, s=string, d=decimal
                $stmt->bind_param(
                    'iissssssssssdisss',
                    $usuarioId,
                    $categoriaId,
                    $dados['titulo'],
                    $dados['descricao'],
                    $dados['data_inicio_evento'],
                    $dados['data_fim_evento'],
                    $dados['hora_inicio'],
                    $dados['hora_fim'],
                    $dados['endereco'],
                    $dados['numero'],
                    $dados['cidade'],
                    $dados['estado'],
                    $dados['cep'],
                    $valor,
                    $vagas,
                    $nomeImagem,
                    $dados['faixa_etaria']
                );

                if ($stmt->execute()) {
                    $sucesso = true;
                    // Limpa os dados do formulário após sucesso.
                    foreach ($dados as $campo => $valorPadrao) {
                        $dados[$campo] = ($campo === 'faixa_etaria') ? 'Livre' : '';
                    }
                } else {
                    // Se o INSERT falhar, remove a imagem que já tinha sido salva
                    // no disco para não deixar arquivo órfão.
                    excluir_imagem_evento($nomeImagem);
                    $erros[] = 'Erro ao salvar o evento: ' . $stmt->error;
                }

                $stmt->close();
            }
        }
    }
}

$categorias = buscar_categorias_eventos($conexao);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventoVivo — Novo evento</title>
  <meta name="theme-color" content="#110f0c">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Courier+Prime:wght@400;700&family=Permanent+Marker&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../Css/crud_eventos.css">
</head>
<?php require dirname(__FILE__) . '/Componentes/header.php'; ?>
<body>

<main class="admin-page evento-form-page">
  <div class="wrap">

    <p class="eyebrow">PAINEL DE CONTROLE</p>
    <h1>NOVO EVENTO</h1>

    <?php if ($sucesso): ?>
      <div class="alert alert-sucesso">
        Evento cadastrado com sucesso!
        <a href="CRUD_Eventos.php">Ver meus eventos</a>
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

    <form class="evento-form" action="CadastrarEvento.php" method="post" enctype="multipart/form-data">

      <div class="campo campo-full">
        <label for="titulo">Título do evento</label>
        <input type="text" id="titulo" name="titulo" maxlength="200" required
               value="<?php echo htmlspecialchars($dados['titulo']); ?>">
      </div>

      <div class="campo campo-full">
        <label for="descricao">Descrição</label>
        <textarea id="descricao" name="descricao"><?php echo htmlspecialchars($dados['descricao']); ?></textarea>
      </div>

      <div class="campo">
        <label for="categoria_id">Categoria</label>
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

      <div class="campo">
        <label for="faixa_etaria">Faixa etária</label>
        <select id="faixa_etaria" name="faixa_etaria">
          <?php foreach (array('Livre', '10', '12', '14', '16', '18+') as $faixa): ?>
            <option value="<?php echo $faixa; ?>" <?php echo ($dados['faixa_etaria'] === $faixa) ? 'selected' : ''; ?>>
              <?php echo ($faixa === 'Livre') ? 'Livre' : $faixa . ' anos'; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="campo">
        <label for="data_inicio_evento">Data de início</label>
        <input type="date" id="data_inicio_evento" name="data_inicio_evento" required
               value="<?php echo htmlspecialchars($dados['data_inicio_evento']); ?>">
      </div>

      <div class="campo">
        <label for="data_fim_evento">Data de término</label>
        <input type="date" id="data_fim_evento" name="data_fim_evento" required
               value="<?php echo htmlspecialchars($dados['data_fim_evento']); ?>">
      </div>

      <div class="campo">
        <label for="hora_inicio">Horário de início</label>
        <input type="time" id="hora_inicio" name="hora_inicio" required
               value="<?php echo htmlspecialchars($dados['hora_inicio']); ?>">
      </div>

      <div class="campo">
        <label for="hora_fim">Horário de término</label>
        <input type="time" id="hora_fim" name="hora_fim" required
               value="<?php echo htmlspecialchars($dados['hora_fim']); ?>">
      </div>

      <div class="campo campo-full">
        <label for="endereco">Endereço</label>
        <input type="text" id="endereco" name="endereco" maxlength="150" required
               value="<?php echo htmlspecialchars($dados['endereco']); ?>">
      </div>

      <div class="campo">
        <label for="numero">Número</label>
        <input type="text" id="numero" name="numero" maxlength="10"
               value="<?php echo htmlspecialchars($dados['numero']); ?>">
      </div>

      <div class="campo">
        <label for="cep">CEP</label>
        <input type="text" id="cep" name="cep" maxlength="9" placeholder="00000-000"
               value="<?php echo htmlspecialchars($dados['cep']); ?>">
      </div>

      <div class="campo">
        <label for="cidade">Cidade</label>
        <input type="text" id="cidade" name="cidade" maxlength="100" required
               value="<?php echo htmlspecialchars($dados['cidade']); ?>">
      </div>

      <div class="campo">
        <label for="estado">Estado (UF)</label>
        <input type="text" id="estado" name="estado" maxlength="2" placeholder="SC" required
               value="<?php echo htmlspecialchars($dados['estado']); ?>">
      </div>

      <div class="campo">
        <label for="valor">Valor (R$)</label>
        <input type="number" id="valor" name="valor" min="0" step="0.01" required
               value="<?php echo htmlspecialchars($dados['valor']); ?>">
      </div>

      <div class="campo">
        <label for="vagas">Vagas</label>
        <input type="number" id="vagas" name="vagas" min="0" step="1" required
               value="<?php echo htmlspecialchars($dados['vagas']); ?>">
      </div>

      <div class="campo campo-full">
        <label for="imagem_capa">Imagem de capa (JPG, PNG ou GIF, até 2MB)</label>
        <input type="file" id="imagem_capa" name="imagem_capa" accept="image/jpeg,image/png,image/gif">
      </div>

      <div class="form-acoes">
        <button type="submit" class="btn btn-primary">CADASTRAR EVENTO</button>
        <a href="CRUD_Eventos.php" class="btn-icone">Cancelar</a>
      </div>

    </form>

  </div>
</main>

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>
</body>
</html>
