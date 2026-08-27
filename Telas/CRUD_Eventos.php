<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EventoVivo — Gerenciar eventos</title>
  <meta name="theme-color" content="#110f0c">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Courier+Prime:wght@400;700&family=Permanent+Marker&display=swap" rel="stylesheet">

  <!-- Ajuste o caminho caso este arquivo fique em outra pasta -->
  <link rel="stylesheet" href="../Css/crud_eventos.css">
</head>
<?php
require dirname(__FILE__) . '/Componentes/header.php';
?>
<body>



<main class="admin-page">

  <section class="page-heading">
    <div class="wrap heading-inner">
      <div>
        <p class="eyebrow">PAINEL DE CONTROLE</p>
        <h1>MEUS EVENTOS</h1>
        <p class="heading-sub">
          Gerencie os eventos publicados pela sua conta.
        </p>
      </div>

      <!-- Futuramente: abrir formulário de INSERT -->
      <button class="btn btn-primary" type="button">
        + NOVO EVENTO
      </button>
    </div>
  </section>

  <section class="events-section">
    <div class="wrap">

      <div class="toolbar">
        <div class="search-box">
          <label for="busca">Buscar evento</label>
          <input id="busca" type="search" placeholder="Nome do evento...">
        </div>

        <div class="filter-box">
          <label for="categoria">Categoria</label>
          <select id="categoria">
            <option>Todas as categorias</option>
            <option>Música</option>
            <option>Teatro</option>
            <option>Dança</option>
            <option>Cultura</option>
            <option>Outros</option>
          </select>
        </div>

        <div class="filter-box">
          <label for="status">Período</label>
          <select id="status">
            <option>Todos</option>
            <option>Próximos eventos</option>
            <option>Eventos encerrados</option>
          </select>
        </div>

        <button class="btn btn-filter" type="button">FILTRAR</button>
      </div>

      <div class="list-header">
        <div>
          <h2>EVENTOS PUBLICADOS</h2>
          <span class="counter"></span>
        </div>
        <span class="sort-label"></span>
      </div>
    </div>
  </section>

</main>

<footer class="site-footer">
  <div class="wrap footer-inner">
    <div class="logo">Evento<span>Vivo</span></div>
    <p class="copy">© 2026 EventoVivo — A cena independente tem endereço.</p>
  </div>
</footer>

</body>
</html>
