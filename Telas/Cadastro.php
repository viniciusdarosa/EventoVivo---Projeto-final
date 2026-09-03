<?php
/**
 * Cadastro.php — Processamento de cadastro + tela
 * Compatível com PHP 5.3.9 (sem short arrays, ??, __DIR__, etc.)
 */
session_start();
require_once dirname(__FILE__) . '/../config/conexao.php';
require_once dirname(__FILE__) . '/Componentes/funcoes_eventos.php';

$erros = array();
$sucesso = false;

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
    // Recupera e faz trim nos campos
    foreach ($dados as $campo => $valorPadrao) {
        if (isset($_POST[$campo])) {
            $dados[$campo] = trim($_POST[$campo]);
        }
    }
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
    $confirma_senha = isset($_POST['confirma_senha']) ? $_POST['confirma_senha'] : '';

    // ---- Validações ----
    if ($dados['nome'] === '') {
        $erros[] = 'Informe seu nome completo.';
    }
    if ($dados['email'] === '' || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Informe um e-mail válido.';
    }
    if ($senha === '') {
        $erros[] = 'Informe uma senha.';
    } elseif (strlen($senha) < 6) {
        $erros[] = 'A senha deve ter pelo menos 6 caracteres.';
    }
    if ($confirma_senha === '') {
        $erros[] = 'Confirme a senha.';
    } elseif ($senha !== $confirma_senha) {
        $erros[] = 'As senhas não conferem.';
    }

    // Verifica se e-mail já existe
    if (empty($erros)) {
        $stmt = $conexao->prepare("SELECT id_usuario FROM usuario WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $dados['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $erros[] = 'Este e-mail já está cadastrado.';
        }
        $stmt->close();
    }

    // ---- Se passou em tudo, insere no banco ----
    if (empty($erros)) {
        $hashSenha = hash_senha($senha); // gera salt + hash no formato sha256:$salt:$hash

        $sql = "INSERT INTO usuario
                    (nome, email, senha, telefone, cpf, data_nascimento,
                     cidade, estado, cep, endereco, numero, complemento,
                     tipo, data_cadastro)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'usuario', NOW())";

        $stmt = $conexao->prepare($sql);
        if ($stmt === false) {
            $erros[] = 'Erro ao preparar cadastro: ' . $conexao->error;
        } else {
            $stmt->bind_param(
                'ssssssssssss',
                $dados['nome'],
                $dados['email'],
                $hashSenha,
                $dados['telefone'],
                $dados['cpf'],
                $dados['data_nascimento'],
                $dados['cidade'],
                $dados['estado'],
                $dados['cep'],
                $dados['endereco'],
                $dados['numero'],
                $dados['complemento']
            );

            if ($stmt->execute()) {
                $sucesso = true;
                $novoId = $conexao->insert_id;
                // Cria sessão automática após cadastro
                session_regenerate_id(true);
                $_SESSION['id_usuario'] = (int) $novoId;
                $_SESSION['nome_usuario'] = $dados['nome'];
            } else {
                $erros[] = 'Erro ao salvar cadastro: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Se já logado, redireciona
if (isset($_SESSION['id_usuario'])) {
    header('Location: CRUD_Eventos.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventoVivo — Cadastre-se</title>
    <meta name="description" content="Crie sua conta EventoVivo, divulgue seu trabalho como artista independente ou descubra eventos perto de você.">
    <meta name="theme-color" content="#110f0c">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' fill='%23110f0c'/%3E%3Ctext x='32' y='46' font-family='Arial, sans-serif' font-weight='900' font-size='38' fill='%23b8000d' text-anchor='middle'%3EV%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Courier+Prime:wght@400;700&family=Permanent+Marker&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style.css">
</head>
<body>

<header class="site-header">
  <div class="wrap header-inner">
    <a class="logo" href="Home.php">Evento<span>Vivo</span></a>
    <nav class="nav">
      <a href="Home.php">Home</a>
      <a href="Eventos.php">Eventos</a>
      <a href="Artistas.php">Artistas</a>
      <a href="Login.php">Entrar</a>
      <a href="Cadastro.php">Cadastrar</a>
    </nav>
    <form class="header-search" action="Busca.php" method="get" role="search">
      <input type="search" name="q" placeholder="Buscar eventos, artistas, cidades..." aria-label="Busca geral">
      <button type="submit" aria-label="Buscar">🔍</button>
    </form>
  </div>
</header>

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

        <?php if ($sucesso): ?>
          <div class="alert alert-sucesso" style="margin-bottom:1rem;padding:.8rem;background:rgba(230,185,35,.12);border:2px solid #e6b923;color:#e6dfc9;font-size:.85rem;">
            Conta criada com sucesso! Redirecionando...
            <script>setTimeout(function(){ window.location.href = 'CRUD_Eventos.php'; }, 1500);</script>
          </div>
        <?php endif; ?>

        <?php if (!empty($erros)): ?>
          <div class="alert alert-erro" style="margin-bottom:1rem;padding:.8rem;background:rgba(179,18,30,.12);border:2px solid #b3121e;color:#e6dfc9;font-size:.85rem;">
            <strong>Corrija os itens abaixo:</strong>
            <ul style="margin:.4rem 0 0;padding-left:1.2rem;">
              <?php foreach ($erros as $erro): ?>
                <li><?php echo htmlspecialchars($erro); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

<form action="Cadastro.php" method="post" autocomplete="on">
        <div class="form-group">
          <label for="nome">Nome completo</label>
          <input type="text" id="nome" name="nome" placeholder="Seu nome" required
                 value="<?php echo htmlspecialchars($dados['nome']); ?>">
        </div>
        <div class="form-group">
          <label for="email">E-mail</label>
          <input type="email" id="email" name="email" placeholder="seu@email.com" required
                 value="<?php echo htmlspecialchars($dados['email']); ?>">
        </div>
        <div class="form-group">
          <label for="senha">Senha</label>
          <input type="password" id="senha" name="senha" placeholder="Crie uma senha (mín. 6 caracteres)" required>
        </div>
        <div class="form-group">
          <label for="confirma-senha">Confirmar senha</label>
          <input type="password" id="confirma-senha" name="confirma_senha" placeholder="Repita a senha" required>
        </div>

        <fieldset style="margin-top:1.5rem;padding:1rem;border:1px solid var(--rule);border-radius:4px;">
          <legend style="font-weight:700;text-transform:uppercase;font-size:.75rem;color:var(--paper-dim);letter-spacing:.06em;">Endereço (opcional)</legend>
          <div class="form-group">
            <label for="cep">CEP</label>
            <input type="text" id="cep" name="cep" maxlength="9" placeholder="00000-000"
                   value="<?php echo htmlspecialchars($dados['cep']); ?>">
          </div>
          <div class="form-group">
            <label for="endereco">Endereço</label>
            <input type="text" id="endereco" name="endereco" maxlength="150" placeholder="Rua, Avenida, etc."
                   value="<?php echo htmlspecialchars($dados['endereco']); ?>">
          </div>
          <div class="form-group">
            <label for="numero">Número</label>
            <input type="text" id="numero" name="numero" maxlength="10" placeholder="Nº"
                   value="<?php echo htmlspecialchars($dados['numero']); ?>">
          </div>
          <div class="form-group">
            <label for="complemento">Complemento</label>
            <input type="text" id="complemento" name="complemento" maxlength="100" placeholder="Apto, Bloco, etc."
                   value="<?php echo htmlspecialchars($dados['complemento']); ?>">
          </div>
          <div class="form-group">
            <label for="cidade">Cidade</label>
            <input type="text" id="cidade" name="cidade" maxlength="100" placeholder="Cidade"
                   value="<?php echo htmlspecialchars($dados['cidade']); ?>">
          </div>
          <div class="form-group">
            <label for="estado">Estado (UF)</label>
            <input type="text" id="estado" name="estado" maxlength="2" placeholder="SC"
                   value="<?php echo htmlspecialchars($dados['estado']); ?>">
          </div>
        </fieldset>

        <fieldset style="margin-top:1.5rem;padding:1rem;border:1px solid var(--rule);border-radius:4px;">
          <legend style="font-weight:700;text-transform:uppercase;font-size:.75rem;color:var(--paper-dim);letter-spacing:.06em;">Dados adicionais (opcional)</legend>
          <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="tel" id="telefone" name="telefone" maxlength="20" placeholder="(00) 00000-0000"
                   value="<?php echo htmlspecialchars($dados['telefone']); ?>">
          </div>
          <div class="form-group">
            <label for="cpf">CPF</label>
            <input type="text" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00"
                   value="<?php echo htmlspecialchars($dados['cpf']); ?>">
          </div>
          <div class="form-group">
            <label for="data_nascimento">Data de nascimento</label>
            <input type="date" id="data_nascimento" name="data_nascimento"
                   value="<?php echo htmlspecialchars($dados['data_nascimento']); ?>">
          </div>
        </fieldset>

        <button type="submit" class="btn-auth">Criar conta</button>
      </form>

      <p class="auth-terms">
        Ao criar sua conta você concorda com os
        <a href="#">Termos de Uso</a> e a
        <a href="#">Politica de Privacidade</a> do EventoVivo.
      </p>

      <div class="auth-divider">ou</div>

      <p class="auth-switch">
        Ja tem conta? <a href="Login.php">Entrar</a>
      </p>
    </div>
  </div>

</main>

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>
