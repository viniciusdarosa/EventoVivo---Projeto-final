<?php
/* ==========================================================
 * COMPONENTE: CadastrarEvento.php
 * Apresenta e processa o cadastro de novos eventos, incluindo validações, categorias e upload da imagem de capa.
 * ========================================================== */

/**
 * CadastrarEvento.php
 *
 * Formulário de cadastro de um novo evento + processamento do
 * INSERT, incluindo o upload da imagem de capa.
 */

require_once dirname(__FILE__) . '/../config/bootstrap.php';
require dirname(__FILE__) . '/Componentes/funcoes_eventos.php';

// Só usuários logados podem cadastrar evento. O id do organizador
// vem da sessão, nunca de um campo do formulário (evita que alguém
// cadastre um evento em nome de outro usuário).
exigir_login('Login.php');
$usuarioId = usuario_id();

// REGRA DE PUBLICAÇÃO (servidor): apenas contas de organizador
// ('empresa' e 'admin') criam eventos. O POST de quem não pode
// publicar nem é processado — esconder o botão é só apresentação.
$podePublicar = usuario_pode_publicar_eventos($conexao);

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $podePublicar) {
    csrf_exigir();

    // Recupera e faz um trim() básico em cada campo de texto
    // (rejeita arrays enviados via ?campo[]=).
    foreach ($dados as $campo => $valorPadrao) {
        $dados[$campo] = entrada_texto($_POST, $campo, $valorPadrao);
    }

    $faixasValidas = array('Livre', '10', '12', '14', '16', '18+');
    if (!in_array($dados['faixa_etaria'], $faixasValidas)) {
        $dados['faixa_etaria'] = 'Livre';
    }

    // ---- Validação server-side completa (compartilhada com a edição) ----
    $erros = validar_dados_evento($conexao, $dados);

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
                registrar_log('Falha ao preparar cadastro de evento: ' . $conexao->error, 'ERROR');
                $erros[] = 'Não foi possível salvar o evento agora. Tente novamente em instantes.';
            } else {
                $categoriaId = (int) $dados['categoria_id'];
                $vagas = (int) $dados['vagas'];
                $valor = (float) $dados['valor'];

                // Tipos: i=inteiro, s=string, d=decimal
                $stmt->bind_param(
                    'iisssssssssssdiss',
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
                    $stmt->close();
                    // Pós-redirecionamento (PRG): evita reenvio do
                    // formulário no F5 e mostra o feedback na listagem.
                    flash_definir('Evento cadastrado com sucesso!');
                    redirecionar('CRUD_Eventos.php');
                } else {
                    // Se o INSERT falhar, remove a imagem que já tinha sido salva
                    // no disco para não deixar arquivo órfão.
                    excluir_imagem_evento($nomeImagem);
                    registrar_log('Falha ao salvar evento: ' . $stmt->error, 'ERROR');
                    $erros[] = 'Não foi possível salvar o evento agora. Tente novamente em instantes.';
                }

                $stmt->close();
            }
        }
    }
}

$categorias = buscar_categorias_eventos($conexao);

$pageTitle = 'EventoVivo — Novo evento';
$pageDescription = 'Cadastre um novo evento no EventoVivo';
$extraCss = array('../Css/crud_eventos.css');

require dirname(__FILE__) . '/Componentes/header.php';
?>

<main class="admin-page evento-form-page">
  <div class="wrap">

    <p class="eyebrow">PAINEL DE CONTROLE</p>
    <h1>NOVO EVENTO</h1>

    <?php if (!$podePublicar): ?>
      <div class="alert alert-erro">
        Somente contas de organizador (tipo <strong>empresa</strong> ou <strong>administração</strong>)
        podem publicar eventos. Sua conta participa da rede como público e artista.
        <a href="Eventos.php">Ver agenda de eventos</a>.
      </div>
    <?php else: ?>

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

    <form class="evento-form" action="CadastrarEvento.php" method="post" enctype="multipart/form-data" data-loading="true">
    <?php echo csrf_campo(); ?>

      <div class="campo campo-full">
        <label for="titulo">Título do evento</label>
        <input type="text" id="titulo" name="titulo" maxlength="200" required
               value="<?php echo htmlspecialchars($dados['titulo']); ?>">
      </div>

      <div class="campo campo-full">
        <label for="descricao">Descrição</label>
        <textarea id="descricao" name="descricao" maxlength="20000" required><?php echo htmlspecialchars($dados['descricao']); ?></textarea>
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
        <input type="number" id="vagas" name="vagas" min="1" step="1" required
               value="<?php echo htmlspecialchars($dados['vagas']); ?>">
      </div>

      <div class="campo campo-full">
        <label for="imagem_capa">Imagem de capa (JPG, PNG ou GIF, até 2MB)</label>
        <input type="file" id="imagem_capa" name="imagem_capa" accept="image/jpeg,image/png,image/gif" required>
      </div>

      <div class="form-acoes">
        <button type="submit" class="btn btn-primary">CADASTRAR EVENTO</button>
        <a href="CRUD_Eventos.php" class="btn-icone">Cancelar</a>
      </div>

    </form>
    <?php endif; ?>

  </div>
</main>

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>