<?php
/* Compatível com PHP 5.2.0: sem __DIR__ (5.3+), sem array curto [] (5.4+),
   sem operador ?? (7.0+) e sem depender de <?= (short_open_tag). */
require_once dirname(__FILE__) . '/Componentes/cards.php';

/* ---- Dados: eventos em destaque ---- */
$eventos = array(
    array(
        'data'   => '23 ago',
        'local'  => 'Porto Alegre, RS',
        'titulo' => 'Noite de Garagem',
        'desc'   => 'Cortina de Ferro sobe ao palco do Galpão 7 pra um set cru, direto e sem enrolação.',
    ),
    array(
        'data'   => '30 ago',
        'local'  => 'São Paulo, SP',
        'titulo' => 'Subsolo Fest',
        'desc'   => 'Quatro bandas, um palco só: hardcore e post-punk lotam a Casa Rachada até de madrugada.',
    ),
    array(
        'data'   => '05 set',
        'local'  => 'Curitiba, PR',
        'titulo' => 'Ruído na Veia',
        'desc'   => 'Máquina Enferrujada estreia faixas novas de noise rock no Bar do Zeca.',
    ),
    array(
        'data'   => '12 set',
        'local'  => 'Belo Horizonte, MG',
        'titulo' => 'Grito Coletivo',
        'desc'   => 'Poesia falada e punk underground dividem o mesmo microfone nessa noite de sarau e show.',
    ),
);

/* ---- Dados: artistas em destaque ---- */
$artistas = array(
    array(
        'foto'       => '../Assets/artistas/cortina-de-ferro.jpg',
        'nome'       => 'Cortina de Ferro',
        'tipo'       => 'Punk rock',
        'local'      => 'Porto Alegre, RS',
        'categorias' => array('Ao vivo', 'Autoral', 'DIY'),
    ),
    array(
        'foto'       => '../Assets/artistas/maquina-enferrujada.jpg',
        'nome'       => 'Máquina Enferrujada',
        'tipo'       => 'Noise rock',
        'local'      => 'Curitiba, PR',
        'categorias' => array('Instrumental', 'Autoral', 'Underground'),
    ),
    array(
        'foto'       => '../Assets/artistas/fita-cassete.jpg',
        'nome'       => 'Fita Cassete',
        'tipo'       => 'Synth punk',
        'local'      => 'Recife, PE',
        'categorias' => array('Ao vivo', 'Eletrônico', 'Retrô'),
    ),
    array(
        'foto'       => '../Assets/artistas/zona-cinza.jpg',
        'nome'       => 'Zona Cinza',
        'tipo'       => 'Post-punk',
        'local'      => 'Belo Horizonte, MG',
        'categorias' => array('Autoral', 'Ao vivo', 'DIY'),
    ),
);

require dirname(__FILE__) . '/Componentes/header.php';
?>

<main>

  <section class="hero" id="topo">
    <div class="wrap">
      <p class="eyebrow">Sem gravadora. Sem empresário.</p>
      <h1>A cena independente tem endereço.</h1>
      <p class="hero-sub">EventoVivo junta artistas e público num só lugar. Divulgue seu trabalho, publique seu show ou ache o próximo point da cidade — sem burocracia, sem intermediário.</p>
      <div class="hero-cta">
        <a class="btn btn-primary" href="#como-funciona">Divulgar meu trabalho</a>
        <a class="btn btn-ghost" href="#eventos">Ver eventos</a>
      </div>
    </div>
  </section>

  <section class="how" id="como-funciona">
    <div class="wrap">
      <h2>Como funciona</h2>
      <ol class="steps">
        <li>
          <span class="num">01</span>
          <h3>Crie seu perfil</h3>
          <p>Artista sobe fotos, bio, agenda e links em poucos minutos.</p>
        </li>
        <li>
          <span class="num">02</span>
          <h3>Publique o evento</h3>
          <p>Data, local e ingresso: seu show fica visível pra cidade toda.</p>
        </li>
        <li>
          <span class="num">03</span>
          <h3>Pesquise e descubra</h3>
          <p>Quem procura entretenimento acha artistas e eventos pela busca.</p>
        </li>
        <li>
          <span class="num">04</span>
          <h3>Feche negócio</h3>
          <p>Contato direto entre artista, produtor e público, sem intermediário.</p>
        </li>
      </ol>
    </div>
  </section>

  <section class="destaque" id="eventos">
    <div class="wrap">
      <h2>Eventos em destaque</h2>
      <div class="grid">
        <?php foreach ($eventos as $evento): ?>
          <?php render_event_card($evento); ?>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="destaque alt" id="artistas">
    <div class="wrap">
      <h2>Artistas em destaque</h2>
      <div class="grid">
        <?php foreach ($artistas as $artista): ?>
          <?php render_artist_card($artista); ?>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

</main>

<?php require dirname(__FILE__) . '/Componentes/footer.php'; ?>