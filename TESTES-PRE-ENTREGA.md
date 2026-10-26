# EventoVivo — Matriz de testes de pré-entrega

Rodada completa de verificação antes da entrega: funcionalidades,
validações, segurança, logs/console, responsividade e acessibilidade,
com verificação em navegador real (Edge headless). Os cenários abaixo
saem de `matriz-entrega.json` (gerado pela própria suíte).

- Execução da suíte principal: 2026-10-23 09:46:50 → 2026-10-23 09:55:51
- Documento relacionado: `TESTES-RESPONSIVOS.md` (relatório de responsividade)

## Ambiente

| Item | Versão |
|------|--------|
| Sistema | Windows + EasyPHP 5.3.9 |
| Apache | 2.2.21 (Win32), porta 8887 |
| MySQL | 5.5.20 (cliente 14.14), banco `eventovivo` utf8mb4 |
| PHP | 5.3.9 (Zend Engine v2.3.0) |
| Navegador | Edge 155.0.4283.62 headless (puppeteer-core 25.16.0) |
| Node.js | v24.14.0 (npm 11.11.0) |

## Como reproduzir

```powershell
# Suítes (scripts em C:\Users\Marilda\AppData\Local\Temp\opencode\ev-test\)
cd C:\Users\Marilda\AppData\Local\Temp\opencode\ev-test
node entrega-testes.js        # 71 cenários (funcional/validação/segurança/logs) -> matriz-entrega.json
node resp-test.js             # 19 páginas x 10 larguras (320-2560) -> relatorio.md + shots/
node a11y-audit.js            # contraste/imagens/inputs/toque/teclado -> relatorio-a11y.md

# Harness CLI de consultas (38 asserções contra o banco)
$env:Path = "C:\Program Files (x86)\EasyPHP-5.3.9"
& "C:\Program Files (x86)\EasyPHP-5.3.9\php\php539x251229095750\php.exe" C:\Users\Marilda\AppData\Local\Temp\opencode\ev-test\consultas-principais.php

# Sintaxe de todos os PHP do projeto
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & "C:\Program Files (x86)\EasyPHP-5.3.9\php\php539x251229095750\php.exe" -n -l $_.FullName }
```

Contas de demonstração (senha `123456`): `lucas.martins@email.com`,
`ana.souza@email.com`, `marcos.henrique@email.com`,
`gabriel.oliveira@email.com`, `contato@eventossul.com` (empresa),
`admin@eventovivo.com` (admin).

## Resumo executivo

| Verificação | Escopo | Resultado |
|-------------|--------|-----------|
| Sintaxe PHP (`php -l -n`) | 35 arquivos `.php` do projeto | **35/35 sem erro** |
| Instalação do banco | `00_criar_banco.sql` + `01_esquema.sql` + `02_dados_demonstracao.sql` em base vazia | **exit 0; 11 tabelas, 14 FKs, 0 órfãos** |
| Integridade referencial | enforcement real de ON DELETE (1452/1062) | **aprovado** |
| Harness de consultas | 38 asserções (listagens, permissões, painel admin, validações) | **38/38 PASS** |
| Suíte de pré-entrega | funcionalidades, validações, segurança, logs/console (matriz abaixo) | **71/71 PASSOU; 0 falhas; 0 não-executados** |
| Responsividade | 19 páginas × 10 larguras (320→2560): overflow horizontal, menu hambúrguer, erros de JS/recurso | **190 execuções: 0 problemas, 0 falhas** |
| Acessibilidade/visual | contraste WCAG AA, deformação de imagens, alvos de toque, fontes de input, menu por teclado | **0 achados (2 alvos inline exentos; teclado OK)** |
| Logs | `logs/php-errors.log`, `logs/app.log`, console do navegador | **0 erros PHP novos; 0 ERRORs; 0 erros de console** |

## Matriz de cenários (71)

### Bloco B — Funcionalidades e fluxos (cadastro, login, favoritos, uploads válidos, busca) — 22/22

| # | Cenário | Procedimento | Esperado | Real | Status |
|---|---------|--------------|----------|------|--------|
| B1 | Cadastro de conta valida | POST Cadastro.php com nome/e-mail/senha/telefone valido/CPF valido/data nascimento (dados validos) | 302 para CRUD_Eventos, flash "Conta criada com sucesso!", usuario gravado no banco e login em seguida aceito | status=200, flash=true, linhas BD=1, login=302 | **PASSOU** |
| B2 | Login de empresa | POST Login.php (contato@eventossul.com / 123456) | 302 para CRUD_Eventos.php com sessao criada | status=302 location=CRUD_Eventos.php | **PASSOU** |
| B3 | Login de administrador | POST Login.php (admin@eventovivo.com / 123456) e GET do destino | 302 para PainelAdmin.php e pagina do painel carrega (200, "Controle do EventoVivo") | login=302 PainelAdmin.php; painel=200; titulo=true | **PASSOU** |
| B4 | Login com senha incorreta | POST Login.php (marcos.henrique@email.com / senha errada) e GET CRUD_Eventos.php em seguida | 200 com erro "E-mail ou senha inválidos." e nenhuma sessao autenticada (GET de area logada continua 302 Login) | status=200, erroExibido=true, areaLogada=302 | **PASSOU** |
| B5 | GET em Logout.php nao encerra a sessao | GET Logout.php (sem POST/CSRF) e em seguida GET CRUD_Eventos.php com o mesmo cookie | 302 para Home.php na chamada GET e a sessao continua valida (CRUD responde 200) | GET Logout=302 -> Home.php; GET CRUD depois=200 | **PASSOU** |
| B6 | Logout real (POST com CSRF) | POST Logout.php com token e em seguida GET CRUD_Eventos.php e GET PainelAdmin.php com o cookie antigo | 302 Home no POST; chamadas seguintes recebem 302 Login (sessao destruida) | POST=302 -> Home.php; CRUD=302; Painel=302 | **PASSOU** |
| B7 | CRUD: cadastrar evento com imagem | Interface: CadastrarEvento.php preenchido (titulo, categoria, datas, horarios, endereco, valor, vagas) + capa JPG e envio | flash "Evento cadastrado com sucesso!", evento criado no banco e visivel na listagem publica | flash=true, BD=1, listagemPublica=true | **PASSOU** |
| B8 | CRUD: editar evento | Interface: EditarEvento.php?id_evento=16 altera o titulo e salva | flash "Evento atualizado com sucesso!" e titulo persistido no banco | flash=true, BD="Entrega - Show Teste EDITADO" | **PASSOU** |
| B9 | CRUD: arquivar evento | Interface: botao Arquivar em CRUD_Eventos (confirm do navegador) para o evento de teste | flash "Evento arquivado.", evento some de Eventos.php e arquivado=1 no banco | flash=true, arquivado=1, somePublico=true | **PASSOU** |
| B10 | CRUD: restaurar evento | Interface: botao Restaurar em CRUD_Eventos para o evento arquivado | flash "Evento restaurado.", evento volta para Eventos.php e arquivado=0 | flash=true, arquivado=0, voltaPublico=true | **PASSOU** |
| B11 | Avaliar artista | POST AvaliarFreelancer.php (user1 -> freelancer 3, nota 5, comentario) com CSRF e redirect interno | 302, flash "Avaliação registrada." e comentario visivel no perfil publico | status=200, flash=true, comentarioVisivel=true, BD=1 | **PASSOU** |
| B12 | Avaliacao duplicada e bloqueada | POST AvaliarFreelancer.php (user1 -> freelancer 4, que ja foi avaliado por user1 na demo) | 302 com flash "Você já avaliou este artista..." e nenhuma linha nova no banco | bloqueio=true, linhas 6->6 | **PASSOU** |
| B13 | Auto-avaliacao e bloqueada | POST AvaliarFreelancer.php (user1 -> freelancer 1, que pertence ao proprio user1) | 302 com flash "Você não pode avaliar o seu próprio perfil." e sem linha no banco | bloqueio=true, linhas 6->6 | **PASSOU** |
| B14 | Contato do organizador para visitante | GET Eventos.php sem sessao | cada card exibe "Entrar para contatar" apontando para Login.php (sem WhatsApp/e-mail exposto) | status=200, linkEntrar=true, semWaMe=true | **PASSOU** |
| B15 | Contato do organizador para logado (nao-dono) | GET Eventos.php com sessao de user1 | detalhe de contato com WhatsApp wa.me/5548996445566 e mailto:contato@eventossul.com | status=200, wa=true, mailto=true | **PASSOU** |
| B16 | Contato do organizador omite para o proprio dono | GET Eventos.php com sessao da empresa dona dos eventos | nenhum bloco de contato nem "Entrar para contatar" nos cards | status=200, semBloco=true, semEntrar=true | **PASSOU** |
| B17 | Busca de eventos por texto | GET Eventos.php?busca=teatro | lista filtrada: exibe "Noite de Teatro" e nao exibe "Festival de Música" | status=200, achaTeatro=true, escondeFestival=true | **PASSOU** |
| B18 | Busca de artistas por texto | GET Artistas.php?busca=lucas | exibe "Lucas Martins" e nao exibe "Ana Beatriz Souza" | status=200, achaLucas=true, escondeAna=true | **PASSOU** |
| B19 | Favoritar evento pela interface | Logado em user1: Eventos.php -> botao "☆ Salvar" no card "Noite de Teatro" -> Meus Favoritos | flash "Adicionado aos seus favoritos.", evento presente em Meus Favoritos e linha no banco | flash=true, emMeusFavoritos=true, BD=1 | **PASSOU** |
| B20 | Remover favorito pela interface | Meus Favoritos -> botao "♥ Salvo" do evento -> verificar listagem e banco | flash "Removido dos seus favoritos.", evento sai da lista e linha removida | flash=true, linhas BD=0 | **PASSOU** |
| B21 | Upload de foto valida no carrossel | Carrossel: enviar JPG real com legenda "entrega-teste portfolio" | flash "Foto adicionada ao carrossel." e linha nova no banco (total = base+1) | flash=true, fotos 1->2 | **PASSOU** |
| B22 | Remover foto do carrossel | Carrossel: botao "Remover" do card da foto recem-adicionada (card com legenda "entrega-teste") | flash "Foto excluída do carrossel.", foto de teste removida e foto demo do artista intacta | flash=true, fotos=1, restantesTeste=0, fotoDemoIntacta=true | **PASSOU** |

### Bloco C — Validações de entrada e regras de negócio (erros de formulário, CRUD de eventos, uploads inválidos, sessão) — 23/23

| # | Cenário | Procedimento | Esperado | Real | Status |
|---|---------|--------------|----------|------|--------|
| C1 | Cadastro: campos obrigatorios vazios | POST Cadastro.php sem nome, e-mail, senha e confirmacao | mensagens "Informe seu nome completo.", "Informe um e-mail válido.", "Informe uma senha." e "Confirme a senha." sem gravar nada | status=200; nome=true, email=true, senha=true, confirma=true | **PASSOU** |
| C2 | Cadastro: telefone invalido | POST Cadastro.php com telefone 12345 (sem DDD/estrutura) | mensagem "Telefone inválido. Use o DDD + número..." | exibiu=true | **PASSOU** |
| C3 | Cadastro: CPF invalido (digitos verificadores) | POST Cadastro.php com CPF 111.111.111-11 (sequencia repetida) | mensagem "CPF inválido. Confira os 11 números..." | exibiu=true | **PASSOU** |
| C4 | Cadastro: CPF duplicado | POST Cadastro.php com CPF do usuario demo 1 (123.456.789-09) | mensagem "Este CPF já está cadastrado em outra conta." antes de qualquer INSERT | exibiu=true | **PASSOU** |
| C5 | Cadastro: e-mail duplicado | POST Cadastro.php com e-mail ja cadastrado (lucas.martins@email.com) | mensagem "Este e-mail já está cadastrado." e nenhuma conta nova | exibiu=true, usuarios 7->7 | **PASSOU** |
| C6 | Cadastro: data de nascimento no futuro | POST Cadastro.php com data_nascimento=2030-01-01 | mensagem "Informe uma data de nascimento válida (não pode ser no futuro)." | exibiu=true | **PASSOU** |
| C7 | Cadastro: senhas nao conferem | POST Cadastro.php com senha e confirmacao diferentes | mensagem "As senhas não conferem." | exibiu=true | **PASSOU** |
| C8 | Evento: data de inicio inexistente | POST CadastrarEvento.php com data_inicio_evento=2026-02-30 | mensagem "A data de início não é uma data válida." e nenhum evento criado | exibiu=true, eventos 6->6 | **PASSOU** |
| C9 | Evento: termino anterior ao inicio | POST CadastrarEvento.php com inicio 2026-12-10 e fim 2026-12-01 (datas validas) | mensagem "A data de término não pode ser anterior à data de início." | exibiu=true | **PASSOU** |
| C10 | Evento: valor negativo | POST CadastrarEvento.php com valor=-5 | mensagem "Informe um valor válido (use 0 para evento gratuito)." | exibiu=true | **PASSOU** |
| C11 | Evento: vagas zeradas | POST CadastrarEvento.php com vagas=0 | mensagem "A quantidade de vagas deve ser pelo menos 1." | exibiu=true | **PASSOU** |
| C12 | Evento: sem imagem de capa | POST CadastrarEvento.php com todos os campos validos e sem arquivo | mensagem "Envie uma imagem de capa para o evento." (upload obrigatorio no cadastro) | exibiu=true | **PASSOU** |
| C13 | Usuario comum nao publica evento | POST CadastrarEvento.php com sessao de conta tipo "usuario" e payload valido | POST nao processado: 200 reexibindo o formulario, sem flash de sucesso e sem INSERT | status=200, flash=false, eventos 6->6 | **PASSOU** |
| C14 | PainelAdmin bloqueia visitante | GET PainelAdmin.php sem sessao | 302 para Login.php | status=302 location=Login.php | **PASSOU** |
| C15 | PainelAdmin bloqueia usuario comum | GET PainelAdmin.php com sessao de conta tipo "usuario" | 302 para CRUD_Eventos.php (nao ve o painel) | status=302 location=CRUD_Eventos.php | **PASSOU** |
| C16 | Estado vazio da listagem de eventos | GET Eventos.php?busca=zzznaoexiste9 | 200 com aviso "Nenhum evento encontrado com esses filtros." e sem erro PHP | status=200, aviso=true, semErroPhp=true | **PASSOU** |
| C17 | Estado vazio da listagem de artistas | GET Artistas.php?busca=zzznaoexiste9 | 200 com aviso "Nenhum artista encontrado com esses filtros." e sem erro PHP | status=200, aviso=true, semErroPhp=true | **PASSOU** |
| C18 | Perfil de artista inexistente | GET PerfilFreelancer.php?id=9999 | 302 para Artistas.php com flash "Artista não encontrado." (sem fatal error) | GET=302 -> Artistas.php; aviso=true | **PASSOU** |
| C19 | Edicao de evento inexistente | GET EditarEvento.php?id_evento=9999 com sessao da empresa | 302 de volta para CRUD_Eventos.php (sem fatal error) | status=302 location=CRUD_Eventos.php | **PASSOU** |
| C20 | Upload acima do limite de 2MB | Carrossel: enviar arquivo .jpg de 2,5MB | erro de upload exibido e nenhuma foto adicionada ao banco | erroExibido=true, texto=Falha ao enviar o arquivo (código 1).  Apresentação musical realizada em evento cultural.  REMOVER ADICIONAR FOTO IMAGEM (OBRIGATÓRIA) SELECIONE A FOTO DO SEU TRABALHO  Formatos aceitos: JPG, PNG ou GIF. Tamanho máximo: 2MB.  LEGENDA (O... | **PASSOU** |
| C21 | Upload com MIME real forjado (conteudo nao-imagem) | Carrossel: enviar arquivo .jpg cujo conteudo e codigo PHP (<?php phpinfo();?>) | erro "conteudo nao e imagem" exibido e nenhuma foto adicionada | erroExibido=true, texto=O arquivo enviado não é uma imagem válida.  Apresentação musical realizada em evento cultural.  REMOVER ADICIONAR FOTO IMAGEM (OBRIGATÓRIA) SELECIONE A FOTO DO SEU TRABALHO  Formatos aceitos: JPG, PNG ou GIF. Tamanho máximo: 2MB.  LEGEN... | **PASSOU** |
| C22 | Upload com extensao nao permitida | Carrossel: enviar arquivo shell.php (extensao fora de jpg/png/gif) | erro "Formato de imagem inválido. Envie JPG, PNG ou GIF." e nada salvo | erroExibido=true, fotos 1->1 | **PASSOU** |
| C23 | Sessao expirada por inatividade (30 min) | Logar user1, envelhecer o arquivo de sessao (ultima_atividade - 1h) em tmp/ e chamar CRUD_Eventos.php | 302 para Login.php e, na pagina de login, aviso "Sua sessão expirou por inatividade." | CRUD=302 -> Login.php; avisoExibido=true | **PASSOU** |

### Bloco D — Segurança (CSRF, SQL injection, XSS, força bruta, redirecionamento, upload executável, headers, logs) — 22/22

| # | Cenário | Procedimento | Esperado | Real | Status |
|---|---------|--------------|----------|------|--------|
| D1 | Upload executavel rejeitado (cenario ofensivo) | Carrossel: enviar shell.php com payload <?php system($_GET["c"]); ?> (nome e conteudo maliciosos) | rejeicao no servidor; nenhum arquivo .php criado em uploads/freelancers e nenhuma linha no banco | rejeitado=true, .php em uploads=0, fotos 1->1 | **PASSOU** |
| D2 | CSRF ausente em POST que altera dados | POST Favoritar.php com sessao valida mas SEM campo csrf | HTTP 403 com "Requisição inválida." e nenhum efeito | status=403, corpo="Requisição inválida. Volte à página anterior, atualize e ten" | **PASSOU** |
| D3 | CSRF com token forjado | POST Favoritar.php com campo csrf=token invalido de 32 chars | HTTP 403 com "Requisição inválida." e nenhum efeito | status=403 | **PASSOU** |
| D4 | CSRF ausente no login | POST Login.php sem sessao anterior e sem campo csrf | HTTP 403 com "Requisição inválida." (autenticacao exige token) | status=403 | **PASSOU** |
| D5 | SQL injection no login | POST Login.php com email injetado ("admin' OR '1'='1' -- ") e qualquer senha, depois GET da area logada | validacao de formato rejeita antes da consulta; 200 com erro generico e NENHUMA sessao autenticada (area logada segue 302) | status=200, erroGenerico=true, areaLogada=302 | **PASSOU** |
| D6 | SQL injection nos filtros de busca | GET Eventos.php?busca=%25' OR '1'='1' --  (payload clasico em LIKE) | payload tratado como texto literal: 200 com estado vazio "Nenhum evento encontrado..." e nenhum erro SQL | status=200, vazio=true, semErroSql=true | **PASSOU** |
| D7 | SQL injection UNION na busca geral | GET Busca.php?q=' UNION SELECT id_usuario, senha FROM usuario -- | 200 com resultados vazios, sem erro SQL e SEM vazamento de hash de senha no HTML | status=200, semHashNoHtml=true, semErroSql=true | **PASSOU** |
| D8 | Open redirect em avaliacao (URL absoluta) | POST AvaliarFreelancer.php com redirect=https://evil.example.com e nota invalida | Location restrito a destino interno: Artistas.php (nunca evil.example.com) | status=302 location=Artistas.php | **PASSOU** |
| D9 | Open redirect com protocolo-relativo | POST AvaliarFreelancer.php com redirect=//evil.example.com | Location cai no padrao interno Artistas.php | status=302 location=Artistas.php | **PASSOU** |
| D10 | Open redirect com javascript: | POST Favoritar.php com redirect=javascript:alert(1) | Location volta para Home.php (padrao interno) | status=302 location=Home.php | **PASSOU** |
| D11 | Redirecionamento interno valido e aceito | POST AvaliarFreelancer.php com redirect=PerfilFreelancer.php?id=3 (destino interno legitimo) | Location mantem exatamente o destino interno informado | status=302 location=PerfilFreelancer.php?id=3 | **PASSOU** |
| D12 | ID inexistente em favorito | POST Favoritar.php com id=99999 (evento inexistente) | 302 com flash "Este evento não está mais disponível." e nenhuma linha criada | flash=true, linhas BD=0 | **PASSOU** |
| D13 | ID adulterado: editar evento de outro usuario | POST EditarEvento.php como user1 tentando alterar o evento 1 (dono: empresa) | 302 sem edicao: evento 1 permanece com o titulo original no banco | status=302 -> CRUD_Eventos.php, titulo antes/depois="Festival de M�sica EventoVivo" / "Festival de M�sica EventoVivo" | **PASSOU** |
| D14 | ID adulterado: arquivar evento de outro usuario | POST ExcluirEvento.php como user1 para o evento 1 (dono: empresa) | flash "sem permissão" e arquivado continua 0 no banco | flash=true, arquivado=0 | **PASSOU** |
| D15 | Avaliacao com ID de artista inexistente | POST AvaliarFreelancer.php com freelancer_id=9999 | flash "Artista não encontrado para avaliação." e nenhuma linha | flash=true, linhas BD=0 | **PASSOU** |
| D16 | Cabecalhos de seguranca HTTP | GET Home.php e inspecionar headers de resposta | X-Content-Type-Options: nosniff, X-Frame-Options: SAMEORIGIN, Referrer-Policy e Permissions-Policy presentes | nosniff=nosniff, frame=SAMEORIGIN, referrer=strict-origin-when-cross-origin, permissions=sim | **PASSOU** |
| D17 | Logs bloqueados na web | GET /logs/app.log e /logs/php-errors.log pelo navegador HTTP | 403 Forbidden nos dois arquivos (nada de log publico) | app.log=403, php-errors.log=403 | **PASSOU** |
| D18 | Uploads bloqueiam execucao de PHP | GET .htaccess effectivo: pedir arquivo .php inexistente em uploads/eventos, uploads/freelancers e uploads/perfil | 403 em todas as pastas (nada executado nem servido) | eventos=403, freelancers=403, perfil=403 | **PASSOU** |
| D19 | Logout por GET nao expulsa (sem CSRF) | GET Logout.php com sessao ativa; depois GET PainelAdmin com a mesma sessao de admin | GET redireciona sem destruir sessao; a protecao real e POST+CSRF (validado no caso "Logout real") | GET Logout=302 -> Home.php; Painel ainda acessivel=true | **PASSOU** |
| D20 | Bloqueio de forca bruta no login | 6 POSTs em Login.php para gabriel.oliveira@email.com: 5 com senha errada e a 6a com a senha CORRETA | 5 falhas registradas e a 6a tentativa bloqueada com "Muitas tentativas falharam..." mesmo com a senha certa | tentativa 6: status=200 bloqueio=true | **PASSOU** |
| D21 | XSS no titulo de evento nao executa | Interface (empresa): criar evento com titulo <script>window.__xssT=1</script> e abrir Eventos.php | script NAO executado (window.__xssT indefinido) e texto exibido escapado no card | executou=false, textoEscapado=true, tagVivaNoHtml=false, linhas BD=1 | **PASSOU** |
| D22 | XSS em comentario de avaliacao nao executa | HTTP: user2 comenta <img src=x onerror=window.__xssA=1> no artista 3; interface abre PerfilFreelancer?id=3 | onerror NAO dispara (window.__xssA indefinido) e o marcado aparece como texto | executou=false, textoEscapado=true, imgAtiva=0 | **PASSOU** |

### Bloco E — Logs, console e integridade de dados — 4/4

| # | Cenário | Procedimento | Esperado | Real | Status |
|---|---------|--------------|----------|------|--------|
| E1 | Sem erros novos no log do PHP | Comparar logs/php-errors.log antes/depois da suíte (o arquivo so cresce por append) | nenhuma linha nova com Fatal/Parse/Warning/Notice/Deprecated | 0 linhas de erro novas | **PASSOU** |
| E2 | App.log sem ERRORs inesperados | Comparar logs/app.log antes/depois e classificar os niveis novos | novas linhas apenas de WARNING esperadas (CSRF/forca bruta/login) e ZERO ERROR | linhas novas=10, WARNINGs esperados=10, ERRORs=0 | **PASSOU** |
| E3 | Console do navegador limpo | pageerror/console.error/HTTP>=400 capturados em todas as paginas da suíte (puppeteer) | zero erros de JavaScript, zero erros de console e zero recursos HTTP>=400 | 0 erros | **PASSOU** |
| E4 | Dados demo preservados apos a suite | Contagens finais no banco (antes da limpeza final), excluindo linhas criadas pelos proprios testes | 5 eventos demo, 5 avaliacoes demo, 5 favoritos, 5 fotos de carrossel (freelancer 1 com a foto demo), 6 usuarios demo | eventos=5, avaliacoes=5, favoritos=5, carrossel=5 (fl1=1, fotoDemo=1), usuarios=6 | **PASSOU** |

## Correções feitas nesta rodada de testes

1. **Bug real de gravação — `Telas/CadastrarEvento.php`:** a string de
   tipos do `bind_param` (`'iissssssssssdisss'`) estava desalinhada
   (16 tipos para 17 variáveis): `cep` ligado como `double` gravava
   `88801` em vez de `88801-000` e `valor` ligado como `int` perdia
   os centavos. Corrigido para `'iisssssssssssdiss'`. Auditoria de
   todos os `bind_param` do site: só este estava desalinhado.
2. **`uploads/eventos/.htaccess`:** sintaxe antiga dava 500 no
   Apache 2.2; corrigido para valer em 2.2 e 2.4 (agente bloqueado
   com 403 — verificado no cenário D "Uploads bloqueiam execucao de PHP").
3. **Dados de demonstração com texto corrompido (mojibake):** o
   import histórico gravou acentos via CP850 ("Música" aparecia
   como "M║sica" no navegador). Reparado: tabelas de demonstração
   apagadas e `02_dados_demonstracao.sql` reimportado com
   `--default-character-set=utf8mb4` (scan HEX de 13 campos de
   texto: **0 ocorrências** após o reparo; bytes servidos via HTTP
   conferidos). Lição documentada em `banco de dados/LEIA-ME.md`.
4. **Foto demo do carrossel `portfolio_lucas.png` restaurada:** o
   teste "Remover foto do carrossel" da suíte clicava no primeiro
   botão de excluir (a foto demo) e apagava linha + arquivo. O
   teste agora mira o card com legenda "entrega-teste" e verifica
   que a foto demo continua presente; arquivo de imagem e linha
   restaurados, e um cenário novo do bloco E ("Dados demo
   preservados apos a suite") congela as contagens (6 usuários, 5
   eventos, 5 avaliações, 5 favoritos, 5 fotos de carrossel).
5. **Ajustes da própria suíte** (não do app): sessões isoladas por
   `browser.createBrowserContext()` (o `newPage()` compartilha
   cookies e o Login.php redireciona quem está logado), preenchimento
   de `type=date/time` via `el.value`, comparações de texto
   case-insensitive (o CSS usa `text-transform: uppercase`) e
   navegação até o card via `closest("article")`.

## Verificação final de logs (após a última execução)

- `logs/php-errors.log`: **nenhuma linha nova** durante toda a rodada
  (as últimas linhas do arquivo são de 15/10, de um script antigo em
  `%TEMP%`, anteriores a este trabalho).
- `logs/app.log`: apenas `WARNING`s esperados (CSRF inválido, falha
  de login, bloqueio de força bruta — todos gerados pelos cenários
  de segurança); **0 `ERROR`s**.
- `logs/login-falhas-*.dat`: nenhum arquivo residual (limpeza da suíte).
- Console do navegador (todas as páginas da suíte): **0 erros**.

## Registros de apoio

- `matriz-entrega.json` (gerado por `entrega-testes.js`, na pasta
  `Temp\opencode\ev-test\`) — dados brutos desta matriz.
- `relatorio.md` + `shots/` — responsividade (`resp-test.js`).
- `relatorio-a11y.md` — acessibilidade (`a11y-audit.js`).
