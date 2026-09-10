<?php
/**
 * EditarEvento.php
 *
 * Carrega um evento existente (via ?id_evento=) e exibe o mesmo
 * formulário do cadastro, pré-preenchido. No POST, processa o
 * UPDATE. O upload de nova imagem é opcional: se o usuário não
 * enviar arquivo novo, a imagem atual é mantida; se enviar, a
 * imagem antiga é apagada do servidor.
 */

session_start();
require dirname(__FILE__) . '/../config/conexao.php';
require dirname(__FILE__) . '/Componentes/funcoes_eventos.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuarioId = (int) $_SESSION['id_usuario'];

// id_evento pode vir tanto do GET (ao abrir a tela pelo link "Editar")
// quanto do POST (reenviado como campo oculto ao submeter o formulário).
$idEvento = isset($_POST['id_evento']) ? $_POST['id_evento'] : (isset($_GET['id_evento']) ? $_GET['id_evento'] : null);

if ($idEvento === null || !ctype_digit((string) $idEvento)) {
    header('Location: CRUD_Eventos.php');
    exit;
}

$idEvento = (int) $idEvento;

// Busca o evento atual. Só permite editar evento que pertence ao
// usuário logado (evita que um usuário edite evento de outro pela URL).
$stmt = $conexao->prepare("SELECT * FROM eventos WHERE id_evento = ? AND usuario_id = ?");
$stmt->bind_param('ii', $idEvento, $usuarioId);
$stmt->execute();
$resultado = $stmt->get_result();
$evento = $resultado->fetch_assoc();
$stmt->close();

if (!$evento) {
    // Evento não existe ou não pertence a este usuário.
    header('Location: CRUD_Eventos.php');
    exit;
}

$erros = array();
$sucesso = false;

// Preenche $dados com os valores atuais do evento (pré-preenchimento).
$dados = array(
    'categoria_id'        => (string) $evento['categoria_id'],
    'titulo'               => $evento['titulo'],
    'descricao'            => $evento['descricao'],
    'data_inicio_evento'   => $evento['data_inicio_evento'],
    'data_fim_evento'      => $evento['data_fim_evento'],
    'hora_inicio'          => substr($evento['hora_inicio'], 0, 5),
    'hora_fim'             => substr($evento['hora_fim'], 0, 5),
    'endereco'             => $evento['endereco'],
    'numero'               => $evento['numero'],
    'cidade'               => $evento['cidade'],
    'estado'               => $evento['estado'],
    'cep'                  => $evento['cep'],
    'valor'                => $evento['valor'],
    'vagas'                => $evento['vagas'],
    'faixa_etaria'         => $evento['faixa_etaria'],
);
$imagemAtual = $evento['imagem_capa'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($dados as $campo => $valorPadrao) {
        if (isset($_POST[$campo])) {
            $dados[$campo] = trim($_POST[$campo]);
        }
    }

    // ---- Mesma validação do cadastro ----
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

    // ---- Upload de nova imagem é OPCIONAL na edição ----
    $uploadInfo = validar_upload_imagem_evento(isset($_FILES['imagem_capa']) ? $_FILES['imagem_capa'] : null);

    if ($uploadInfo['enviado'] && !$uploadInfo['ok']) {
        $erros[] = $uploadInfo['erro'];
    }

    if (empty($erros)) {
        $nomeImagemFinal = $imagemAtual;

        // Só mexe na imagem se o usuário realmente enviou uma nova.
        if ($uploadInfo['enviado']) {
            $novoNomeImagem = salvar_upload_imagem_evento($_FILES['imagem_capa'], $uploadInfo['extensao']);

            if ($novoNomeImagem === false) {
                $erros[] = 'Não foi possível salvar a nova imagem. Tente novamente.';
            } else {
                // Apaga o arquivo antigo do servidor antes de gravar o novo nome.
                excluir_imagem_evento($imagemAtual);
                $nomeImagemFinal = $novoNomeImagem;
            }
        }

        if (empty($erros)) {
            $sql = "UPDATE eventos SET
                        categoria_id = ?, titulo = ?, descricao = ?,
                        data_inicio_evento = ?, data_fim_evento = ?,
                        hora_inicio = ?, hora_fim = ?,
                        endereco = ?, numero = ?, cidade = ?, estado = ?, cep = ?,
                        valor = ?, vagas = ?, imagem_capa = ?, faixa_etaria = ?
                    WHERE id_evento = ? AND usuario_id = ?";

            $stmt = $conexao->prepare($sql);

            if ($stmt === false) {
                $erros[] = 'Erro ao preparar a operação: ' . $conexao->error;
            } else {
                $categoriaId = (int) $dados['categoria_id'];
                $vagas = (int) $dados['vagas'];
                $valor = (float) $dados['valor'];

                $stmt->bind_param(
                    'isssssssssssdissii',
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
                    $nomeImagemFinal,
                    $dados['faixa_etaria'],
                    $idEvento,
                    $usuarioId
                );

                if ($stmt->execute()) {
                    $sucesso = true;
                    $imagemAtual = $nomeImagemFinal;
                } else {
                    $erros[] = 'Erro ao atualizar o evento: ' . $stmt->error;
                }

                $stmt->close();
            }
        }
    }
}

$categorias = buscar_categorias_eventos($conexao);

$pageTitle = 'EventoVivo — Editar evento';
$pageDescription = 'Edite seu evento no EventoVivo';
$extraCss = array('../Css/crud_eventos.css');

require dirname(__FILE__) . '/Componentes/header.php';
?>

<main class="admin-page evento-form-page">
  <div class="wrap">

    <p class="eyebrow">PAINEL DE CONTROLE</p>
    <h1>EDITAR EVENTO</h1>

    <?php if ($sucesso): ?>
      <div class="alert alert-sucesso">
        Evento atualizado com sucesso!
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

    <form class="evento-form" action="EditarEvento.php" method="post" enctype="multipart/form-data">

      <input type="hidden" name="id_evento" value="<?php echo (int) $idEvento; ?>">

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
        <label for="imagem_capa">Imagem de capa (deixe em branco para manter a atual)</label>

        <div class="imagem-atual">
          <img src="<?php echo htmlspecialchars(evento_imagem_src($imagemAtual)); ?>" alt="Imagem atual do evento">
          <input type="file" id="imagem_capa" name="imagem_capa" accept="image/jpeg,image/png,image/gif">
        </div>
      </div>

      <div class="form-acoes">
        <button type="submit" class="btn btn-primary">SALVAR ALTERAÇÕES</button>
        <a href="CRUD_Eventos.php" class="btn-icone">Cancelar</a>
      </div>

    </form>

  </div>
</main>

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>
