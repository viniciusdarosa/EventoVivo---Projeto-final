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

      <form action="Cadastro.php" method="post" autocomplete="on">
        <div class="form-group">
          <label for="nome">Nome completo</label>
          <input type="text" id="nome" name="nome" placeholder="Seu nome" required>
        </div>
        <div class="form-group">
          <label for="email">E-mail</label>
          <input type="email" id="email" name="email" placeholder="seu@email.com" required>
        </div>
        <div class="form-group">
          <label for="senha">Senha</label>
          <input type="password" id="senha" name="senha" placeholder="Crie uma senha" required>
        </div>
        <div class="form-group">
          <label for="confirma-senha">Confirmar senha</label>
          <input type="password" id="confirma-senha" name="confirma_senha" placeholder="Repita a senha" required>
        </div>
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

</body>
</html>
