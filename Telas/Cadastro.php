<?php
/* ==========================================================
 * COMPONENTE: Cadastro.php
 * Processa o cadastro de novos usuários e apresenta o formulário de criação da conta.
 * ========================================================== */

/**
 * Cadastro.php — Processamento de cadastro + tela
 */
require_once dirname(__FILE__) . '/../config/bootstrap.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_eventos.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_contato.php';

$erros = array();
$errosPorCampo = array();

// Valores para repopular o formulário
$dados = array(
    'nome' => '',
    'email' => '',
    'telefone' => '',
    'cpf' => '',
    'data_nascimento' => '',
    'cidade' => '',
    'estado' => '',
    'cep' => '',
    'endereco' => '',
    'numero' => '',
    'complemento' => '',
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_exigir();

    // Recupera e faz trim nos campos (rejeita arrays enviados via ?campo[]=)
    foreach ($dados as $campo => $valorPadrao) {
        $dados[$campo] = entrada_texto($_POST, $campo);
    }
    $senha = isset($_POST['senha']) && is_string($_POST['senha']) ? $_POST['senha'] : '';
    $confirma_senha = isset($_POST['confirma_senha']) && is_string($_POST['confirma_senha']) ? $_POST['confirma_senha'] : '';

    // ---- Validações ----
    if ($dados['nome'] === '') {
        $errosPorCampo['nome'] = 'Informe seu nome completo.';
        $erros[] = $errosPorCampo['nome'];
    }
    if ($dados['email'] === '' || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $errosPorCampo['email'] = 'Informe um e-mail válido.';
        $erros[] = $errosPorCampo['email'];
    }
    if ($senha === '') {
        $errosPorCampo['senha'] = 'Informe uma senha.';
        $erros[] = $errosPorCampo['senha'];
    } elseif (strlen($senha) < 6) {
        $errosPorCampo['senha'] = 'A senha deve ter pelo menos 6 caracteres.';
        $erros[] = $errosPorCampo['senha'];
    }
    if ($confirma_senha === '') {
        $errosPorCampo['confirma_senha'] = 'Confirme a senha.';
        $erros[] = $errosPorCampo['confirma_senha'];
    } elseif ($senha !== $confirma_senha) {
        $errosPorCampo['confirma_senha'] = 'As senhas não conferem.';
        $erros[] = $errosPorCampo['confirma_senha'];
    }

    // Os dados complementares são opcionais, mas precisam ter formato válido quando informados.
    $cepDigitos = preg_replace('/\D/', '', $dados['cep']);
    if ($dados['cep'] !== '' && strlen($cepDigitos) !== 8) {
        $errosPorCampo['cep'] = 'Digite um CEP com 8 números.';
        $erros[] = $errosPorCampo['cep'];
    }
    // Telefone: normaliza no servidor (remove pontuação/DDI 55) e valida
    // a estrutura (fixo 10 dígitos ou celular 11 com DDD). O JavaScript da
    // máscara é conforto — aqui é que a validação vale.
    if ($dados['telefone'] !== '') {
        if (!telefone_valido($dados['telefone'])) {
            $errosPorCampo['telefone'] = 'Telefone inválido. Use o DDD + número: fixo (11) 1234-5678 ou celular (11) 91234-5678.';
            $erros[] = $errosPorCampo['telefone'];
        } else {
            // Guarda sempre no formato de exibição, consistente para reexibir.
            $dados['telefone'] = telefone_formatar($dados['telefone']);
        }
    }

    // CPF: valida os dois dígitos verificadores (algoritmo oficial),
    // além dos 11 números e da regra contra sequências repetidas.
    if ($dados['cpf'] !== '') {
        if (!cpf_valido($dados['cpf'])) {
            $errosPorCampo['cpf'] = 'CPF inválido. Confira os 11 números, incluindo os dois dígitos verificadores.';
            $erros[] = $errosPorCampo['cpf'];
        } else {
            $dados['cpf'] = cpf_formatar($dados['cpf']);
        }
    }

    if ($dados['estado'] !== '' && !preg_match('/^[a-zA-Z]{2}$/', $dados['estado'])) {
        $errosPorCampo['estado'] = 'Digite a sigla do estado com 2 letras.';
        $erros[] = $errosPorCampo['estado'];
    } else {
        $dados['estado'] = strtoupper($dados['estado']);
    }

    // Data de nascimento: precisa ser uma data real e não pode ser
    // futura (quando informada — o campo é opcional).
    if ($dados['data_nascimento'] !== ''
        && (!evento_data_valida($dados['data_nascimento'])
            || $dados['data_nascimento'] > date('Y-m-d'))) {
        $errosPorCampo['data_nascimento'] = 'Informe uma data de nascimento válida (não pode ser no futuro).';
        $erros[] = $errosPorCampo['data_nascimento'];
    }

    // Limites de tamanho do servidor (o maxlength é só no HTML):
    // espelham o tamanho das colunas da tabela usuario.
    $limites = array(
        'nome'        => 120,
        'email'       => 150,
        'cidade'      => 100,
        'endereco'    => 150,
        'numero'      => 10,
        'complemento' => 100,
        'cep'         => 9,
        'telefone'    => 20,
        'cpf'         => 14,
        'data_nascimento' => 10,
    );
    foreach ($limites as $campoLimite => $tamanhoMaximo) {
        if (strlen($dados[$campoLimite]) > $tamanhoMaximo) {
            $errosPorCampo[$campoLimite] = 'Este campo pode ter no máximo ' . $tamanhoMaximo . ' caracteres.';
            $erros[] = $errosPorCampo[$campoLimite];
        }
    }

    // Verifica se e-mail já existe
    if (empty($erros)) {
        $stmt = $conexao->prepare("SELECT id_usuario FROM usuario WHERE email = ? LIMIT 1");
        if ($stmt === false) {
            registrar_log('Falha ao preparar consulta de e-mail: ' . $conexao->error, 'ERROR');
            $erros[] = 'Não foi possível concluir o cadastro agora. Tente novamente em instantes.';
        } else {
            $stmt->bind_param('s', $dados['email']);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errosPorCampo['email'] = 'Este e-mail já está cadastrado.';
                $erros[] = $errosPorCampo['email'];
            }
            $stmt->close();
        }
    }

    // CPF é UNIQUE no banco: avisa ANTES do INSERT para o usuário
    // receber a mensagem certa (em vez de um erro genérico).
    if (empty($erros) && $dados['cpf'] !== '') {
        $stmt = $conexao->prepare("SELECT id_usuario FROM usuario WHERE cpf = ? LIMIT 1");
        if ($stmt === false) {
            registrar_log('Falha ao preparar consulta de CPF: ' . $conexao->error, 'ERROR');
            $erros[] = 'Não foi possível concluir o cadastro agora. Tente novamente em instantes.';
        } else {
            $stmt->bind_param('s', $dados['cpf']);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errosPorCampo['cpf'] = 'Este CPF já está cadastrado em outra conta.';
                $erros[] = $errosPorCampo['cpf'];
            }
            $stmt->close();
        }
    }

    // ---- Se passou em tudo, insere no banco ----
    if (empty($erros)) {
        $hashSenha = hash_senha($senha); // bcrypt ($2y$) via password_hash

        // Campos opcionais em branco viram NULL: o UNIQUE de cpf
        // aceita vários NULLs, mas colidiria com strings vazias
        // repetidas ("Duplicate entry '' for key 'cpf'").
        $paraInserir = $dados;
        $camposOpcionais = array(
            'telefone', 'cpf', 'data_nascimento', 'cidade', 'estado',
            'cep', 'endereco', 'numero', 'complemento',
        );
        foreach ($camposOpcionais as $campoOpcional) {
            if ($paraInserir[$campoOpcional] === '') {
                $paraInserir[$campoOpcional] = null;
            }
        }

        $sql = "INSERT INTO usuario
                    (nome, email, senha, telefone, cpf, data_nascimento,
                     cidade, estado, cep, endereco, numero, complemento,
                     tipo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'usuario')";

        $stmt = $conexao->prepare($sql);
        if ($stmt === false) {
            registrar_log('Falha ao preparar cadastro: ' . $conexao->error, 'ERROR');
            $erros[] = 'Não foi possível concluir o cadastro agora. Tente novamente em instantes.';
        } else {
            $stmt->bind_param(
                'ssssssssssss',
                $paraInserir['nome'],
                $paraInserir['email'],
                $hashSenha,
                $paraInserir['telefone'],
                $paraInserir['cpf'],
                $paraInserir['data_nascimento'],
                $paraInserir['cidade'],
                $paraInserir['estado'],
                $paraInserir['cep'],
                $paraInserir['endereco'],
                $paraInserir['numero'],
                $paraInserir['complemento']
            );

            if ($stmt->execute()) {
                $novoId = $conexao->insert_id;
                $stmt->close();
                // Cria sessão automática após cadastro
                session_regenerate_id(true);
                // Rotaciona o token CSRF junto com o login.
                unset($_SESSION['csrf_token']);
                $_SESSION['id_usuario'] = (int) $novoId;
                $_SESSION['nome_usuario'] = $dados['nome'];
                $_SESSION['tipo_usuario'] = 'usuario';
                // PRG: redireciona com feedback, evitando reenvio do
                // formulário no F5 (que rejeitaria o token CSRF antigo).
                flash_definir('Conta criada com sucesso! Bem-vindo(a) ao EventoVivo.');
                redirecionar('CRUD_Eventos.php');
            } else {
                registrar_log('Falha ao salvar cadastro: ' . $stmt->error, 'ERROR');
                $erros[] = 'Não foi possível concluir o cadastro agora. Tente novamente em instantes.';
            }
            $stmt->close();
        }
    }
}

// Se já logado (o caminho de sucesso já saiu com redirecionar acima),
// não mostra o formulário de cadastro de novo.
if (isset($_SESSION['id_usuario'])) {
    redirecionar('CRUD_Eventos.php');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <script>document.documentElement.classList.add('js');</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventoVivo — Cadastre-se</title>
    <meta name="description" content="Crie sua conta EventoVivo, divulgue seu trabalho como artista independente ou descubra eventos perto de você.">
    <meta name="theme-color" content="#120f0f">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' fill='%23120f0f'/%3E%3Ctext x='32' y='46' font-family='Arial, sans-serif' font-weight='900' font-size='38' fill='%23b3121e' text-anchor='middle'%3EV%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Courier+Prime:wght@400;700&family=Permanent+Marker&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Css/tokens.css">
    <link rel="stylesheet" href="../Css/reset.css">
    <link rel="stylesheet" href="../Css/layout.css">
    <link rel="stylesheet" href="../Css/components.css">
    <link rel="stylesheet" href="../Css/site.css">
    <link rel="stylesheet" href="../Css/auth.css">
    <script src="Js/menu.js" defer></script>
</head>
<body>



<div class="auth-topo">
  <a href="Home.php">&larr; Voltar para Home</a>
</div>

<main class="auth-split">

  <div class="auth-brand">
    <p class="brand-logo">Evento<span>Vivo</span></p>
    <h1 class="brand-headline">Junte-se à cena independente.</h1>
    <p class="brand-sub">Crie sua conta em minutos. Seja artista querendo divulgar o próximo show, ou público procurando o melhor da cena autoral da sua cidade.</p>
    <p class="brand-accent">Publique. Divulgue. Conecte.</p>
  </div>

  <div class="auth-form-panel">
<div class="auth-card">
        <h2>Criar conta</h2>

        <?php if (!empty($erros)): ?>
          <div class="alert alert-erro" id="cadastro-erros" tabindex="-1">
            <strong>Corrija os itens abaixo:</strong>
            <ul>
              <?php foreach ($erros as $erro): ?>
                <li><?php echo htmlspecialchars($erro); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

<form action="Cadastro.php" method="post" autocomplete="on" id="form-cadastro" novalidate data-loading="true">
        <?php echo csrf_campo(); ?>
        <div class="form-group">
          <label for="nome">Nome completo <span class="campo-obrigatorio">Obrigatório</span></label>
          <input type="text" id="nome" name="nome" placeholder="Seu nome" required autocomplete="name" aria-invalid="<?php echo isset($errosPorCampo['nome']) ? 'true' : 'false'; ?>"<?php if (isset($errosPorCampo['nome'])): ?> aria-describedby="erro-nome"<?php endif; ?>
                 value="<?php echo htmlspecialchars($dados['nome']); ?>">
          <?php if (isset($errosPorCampo['nome'])): ?><small class="erro-campo" id="erro-nome"><?php echo htmlspecialchars($errosPorCampo['nome']); ?></small><?php endif; ?>
        </div>
        <div class="form-group">
          <label for="email">E-mail <span class="campo-obrigatorio">Obrigatório</span></label>
          <input type="email" id="email" name="email" placeholder="seu@email.com" required autocomplete="email" aria-invalid="<?php echo isset($errosPorCampo['email']) ? 'true' : 'false'; ?>"<?php if (isset($errosPorCampo['email'])): ?> aria-describedby="erro-email"<?php endif; ?>
                 value="<?php echo htmlspecialchars($dados['email']); ?>">
          <?php if (isset($errosPorCampo['email'])): ?><small class="erro-campo" id="erro-email"><?php echo htmlspecialchars($errosPorCampo['email']); ?></small><?php endif; ?>
        </div>
        <div class="form-group">
          <label for="senha">Senha <span class="campo-obrigatorio">Obrigatório</span></label>
          <p class="ajuda-campo" id="ajuda-senha">Use pelo menos 6 caracteres.</p>
          <div class="campo-senha">
            <input type="password" id="senha" name="senha" placeholder="Crie uma senha" minlength="6" autocomplete="new-password" required aria-describedby="ajuda-senha<?php echo isset($errosPorCampo['senha']) ? ' erro-senha' : ''; ?>" aria-invalid="<?php echo isset($errosPorCampo['senha']) ? 'true' : 'false'; ?>">
            <button type="button" class="alternar-senha" data-target="senha" aria-label="Mostrar senha" aria-pressed="false">Mostrar</button>
          </div>
          <?php if (isset($errosPorCampo['senha'])): ?><small class="erro-campo" id="erro-senha"><?php echo htmlspecialchars($errosPorCampo['senha']); ?></small><?php endif; ?>
        </div>
        <div class="form-group">
          <label for="confirma-senha">Confirmar senha <span class="campo-obrigatorio">Obrigatório</span></label>
          <div class="campo-senha">
            <input type="password" id="confirma-senha" name="confirma_senha" placeholder="Repita a senha" minlength="6" autocomplete="new-password" required aria-describedby="estado-senhas" aria-invalid="<?php echo isset($errosPorCampo['confirma_senha']) ? 'true' : 'false'; ?>">
            <button type="button" class="alternar-senha" data-target="confirma-senha" aria-label="Mostrar confirmação de senha" aria-pressed="false">Mostrar</button>
          </div>
          <small class="estado-senhas" id="estado-senhas" aria-live="polite"><?php echo isset($errosPorCampo['confirma_senha']) ? htmlspecialchars($errosPorCampo['confirma_senha']) : ''; ?></small>
        </div>

        <details class="grupo-opcional"<?php if (isset($errosPorCampo['cep']) || isset($errosPorCampo['estado'])): ?> open<?php endif; ?>>
          <summary>Endereço <span class="campo-opcional">Opcional</span></summary>
          <fieldset>
          <div class="form-group">
          <label for="cep">CEP <span class="campo-opcional">Opcional</span></label>
          <input type="text" id="cep" name="cep" maxlength="9" inputmode="numeric" autocomplete="postal-code" placeholder="00000-000" aria-invalid="<?php echo isset($errosPorCampo['cep']) ? 'true' : 'false'; ?>"<?php if (isset($errosPorCampo['cep'])): ?> aria-describedby="erro-cep"<?php endif; ?>
                   value="<?php echo htmlspecialchars($dados['cep']); ?>">
            <?php if (isset($errosPorCampo['cep'])): ?><small class="erro-campo" id="erro-cep"><?php echo htmlspecialchars($errosPorCampo['cep']); ?></small><?php endif; ?>
          </div>
          <div class="form-group">
            <label for="endereco">Endereço <span class="campo-opcional">Opcional</span></label>
            <input type="text" id="endereco" name="endereco" maxlength="150" placeholder="Rua, Avenida, etc."
                   value="<?php echo htmlspecialchars($dados['endereco']); ?>">
          </div>
          <div class="form-group">
            <label for="numero">Número <span class="campo-opcional">Opcional</span></label>
            <input type="text" id="numero" name="numero" maxlength="10" placeholder="Nº"
                   value="<?php echo htmlspecialchars($dados['numero']); ?>">
          </div>
          <div class="form-group">
            <label for="complemento">Complemento <span class="campo-opcional">Opcional</span></label>
            <input type="text" id="complemento" name="complemento" maxlength="100" placeholder="Apto, Bloco, etc."
                   value="<?php echo htmlspecialchars($dados['complemento']); ?>">
          </div>
          <div class="form-group">
            <label for="cidade">Cidade <span class="campo-opcional">Opcional</span></label>
            <input type="text" id="cidade" name="cidade" maxlength="100" autocomplete="address-level2" placeholder="Cidade"
                   value="<?php echo htmlspecialchars($dados['cidade']); ?>">
          </div>
          <div class="form-group">
            <label for="estado">Estado (UF) <span class="campo-opcional">Opcional</span></label>
            <input type="text" id="estado" name="estado" maxlength="2" pattern="[A-Za-z]{2}" autocomplete="address-level1" placeholder="SC" aria-invalid="<?php echo isset($errosPorCampo['estado']) ? 'true' : 'false'; ?>"<?php if (isset($errosPorCampo['estado'])): ?> aria-describedby="erro-estado"<?php endif; ?>
                   value="<?php echo htmlspecialchars($dados['estado']); ?>">
            <?php if (isset($errosPorCampo['estado'])): ?><small class="erro-campo" id="erro-estado"><?php echo htmlspecialchars($errosPorCampo['estado']); ?></small><?php endif; ?>
          </div>
          </fieldset>
        </details>

        <details class="grupo-opcional"<?php if (isset($errosPorCampo['telefone']) || isset($errosPorCampo['cpf'])): ?> open<?php endif; ?>>
          <summary>Dados adicionais <span class="campo-opcional">Opcional</span></summary>
          <fieldset>
          <div class="form-group">
          <label for="telefone">Telefone (fixo ou celular) <span class="campo-opcional">Opcional</span></label>
          <p class="ajuda-campo" id="ajuda-telefone">Com DDD. Ex.: (11) 1234-5678 (fixo) ou (11) 91234-5678 (celular).</p>
          <input type="tel" id="telefone" name="telefone" maxlength="20" inputmode="tel" autocomplete="tel" placeholder="(11) 91234-5678" aria-invalid="<?php echo isset($errosPorCampo['telefone']) ? 'true' : 'false'; ?>"<?php if (isset($errosPorCampo['telefone'])): ?> aria-describedby="ajuda-telefone erro-telefone"<?php else: ?> aria-describedby="ajuda-telefone"<?php endif; ?>
                   value="<?php echo htmlspecialchars($dados['telefone']); ?>">
            <?php if (isset($errosPorCampo['telefone'])): ?><small class="erro-campo" id="erro-telefone"><?php echo htmlspecialchars($errosPorCampo['telefone']); ?></small><?php endif; ?>
          </div>
          <div class="form-group">
          <label for="cpf">CPF <span class="campo-opcional">Opcional</span></label>
          <p class="ajuda-campo" id="ajuda-cpf">Se informar, precisa ser um CPF válido (11 números com dígitos verificadores).</p>
          <input type="text" id="cpf" name="cpf" maxlength="14" inputmode="numeric" autocomplete="off" placeholder="000.000.000-00" aria-invalid="<?php echo isset($errosPorCampo['cpf']) ? 'true' : 'false'; ?>"<?php if (isset($errosPorCampo['cpf'])): ?> aria-describedby="ajuda-cpf erro-cpf"<?php else: ?> aria-describedby="ajuda-cpf"<?php endif; ?>
                   value="<?php echo htmlspecialchars($dados['cpf']); ?>">
            <?php if (isset($errosPorCampo['cpf'])): ?><small class="erro-campo" id="erro-cpf"><?php echo htmlspecialchars($errosPorCampo['cpf']); ?></small><?php endif; ?>
          </div>
          <div class="form-group">
            <label for="data_nascimento">Data de nascimento <span class="campo-opcional">Opcional</span></label>
            <input type="date" id="data_nascimento" name="data_nascimento"
                   value="<?php echo htmlspecialchars($dados['data_nascimento']); ?>">
          </div>
          </fieldset>
        </details>
        <button type="submit" class="btn-auth">Criar conta</button>
      </form>

      <p class="auth-terms">
        Ao criar sua conta você concorda com as regras de uso e
        de privacidade do EventoVivo.
      </p>

      <div class="auth-divider">ou</div>

      <p class="auth-switch">
        Ja tem conta? <a href="Login.php">Entrar</a>
      </p>
    </div>
  </div>

</main>

<script src="Js/mascaras.js"></script>
<script>
(function () {
  var form = document.getElementById('form-cadastro');
  if (!form) return;

  var cep = document.getElementById('cep');
  var telefone = document.getElementById('telefone');
  var cpf = document.getElementById('cpf');
  var estado = document.getElementById('estado');

  // Máscaras com normalização de dígitos, apagagem por dígito e cursor
  // estável (digitar, colar, editar e apagar). Se o arquivo não carregar,
  // nada quebra: o servidor valida e normaliza tudo de novo.
  if (window.Mascaras) {
    window.Mascaras.telefone(telefone);
    window.Mascaras.cpf(cpf);
    window.Mascaras.cep(cep);
  }

  estado.addEventListener('input', function () { this.value = this.value.replace(/[^a-z]/gi, '').substring(0, 2).toUpperCase(); });

  var senha = document.getElementById('senha');
  var confirma = document.getElementById('confirma-senha');
  var avisoSenhas = document.getElementById('estado-senhas');
  function validarConfirmacao() {
    var divergente = confirma.value !== '' && senha.value !== confirma.value;
    confirma.setCustomValidity(divergente ? 'As senhas não conferem.' : '');
    confirma.setAttribute('aria-invalid', divergente ? 'true' : 'false');
    avisoSenhas.textContent = divergente ? 'As senhas não conferem.' : '';
    return !divergente;
  }
  senha.addEventListener('input', validarConfirmacao);
  confirma.addEventListener('input', validarConfirmacao);

  var toggles = form.querySelectorAll('.alternar-senha');
  for (var i = 0; i < toggles.length; i++) {
    toggles[i].addEventListener('click', function () {
      var campo = document.getElementById(this.getAttribute('data-target'));
      var mostrar = campo.type === 'password';
      campo.type = mostrar ? 'text' : 'password';
      this.textContent = mostrar ? 'Ocultar' : 'Mostrar';
      this.setAttribute('aria-pressed', mostrar ? 'true' : 'false');
      this.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');
    });
  }

  form.addEventListener('submit', function (event) {
    if (!validarConfirmacao() || !form.checkValidity()) {
      event.preventDefault();
      form.reportValidity();
    }
  });

  var campoComErro = form.querySelector('[aria-invalid="true"]');
  if (campoComErro) campoComErro.focus();
  else {
    var resumo = document.getElementById('cadastro-erros');
    if (resumo) resumo.focus();
  }
}());
</script>

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>
