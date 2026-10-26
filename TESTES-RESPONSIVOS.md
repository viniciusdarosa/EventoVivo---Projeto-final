# Testes responsivos e de acessibilidade — EventoVivo

Registro dos dispositivos, larguras e verificações executados na modernização
visual. Testes automatizados em navegador real (Microsoft Edge headless via
`puppeteer-core`) contra o servidor local (Apache `127.0.0.1:8887` + MySQL).

- **Data da última rodada:** 22/10/2026
- **Navegador:** Edge/155.0.4283.62 (headless)
- **Resultado final:** 190/190 execuções OK — 0 falhas de layout, 0 erros de
  JS, 0 recurso 404, 0 erro PHP renderizado

## Dispositivos e larguras testados

| Largura | Resolução | Perfil |
|---|---|---|
| 320 px | 320×568 | iPhone SE 1ª geração / mini |
| 360 px | 360×640 | Android comum |
| 390 px | 390×844 | iPhone 14/15 |
| 430 px | 430×932 | iPhone Pro Max |
| 768 px | 768×1024 | iPad retrato (tablet) |
| 1024 px | 1024×768 | iPad paisagem / pequeno desktop |
| 1280 px | 1280×800 | Notebook |
| 1440 px | 1440×900 | Monitor largo |
| 1920 px | 1920×1080 | Full HD |
| 2560 px | 2560×1440 | QHD |

## Páginas cobertas (19 combinações página × contexto)

- **Deslogado (7):** Home, Eventos, Artistas, Busca (`?q=festa`), Login,
  Cadastro, PerfilFreelancer (`id=1`)
- **Admin (1):** PainelAdmin
- **Empresa (8):** CRUD_Eventos, CadastrarEvento, EditarEvento
  (`id_evento=2`), CRUD_Freelancers, EditarFreelancer, CarrosselFreelancer,
  MeusFavoritos, PerfilFreelancer (`id=5`, visão do dono)
- **Usuário comum (3):** Home, PerfilFreelancer (`id=1`, visão do dono),
  MeusFavoritos

19 combinações × 10 larguras = **190 execuções**.

## O que cada execução verifica

1. **Rolagem horizontal indesejada:** `document.scrollWidth − viewport ≤ 1px`
2. **Elementos fora da tela:** retângulos de qualquer elemento passando da
   borda direita/esquerda (ignora containers com `overflow-x: auto/scroll`
   próprios, como as tabelas do painel)
3. **Status HTTP 200** e ausência de redirecionamento inesperado
4. **Erros de JavaScript** (console + exceções) e **recurso CSS/JS com 404**
5. **Texto de erro PHP** visível na página (Warning/Fatal/Notice)
6. **Menu por largura:** abaixo de 1100px o hambúrguer aparece e a navegação
   fica oculta; a partir de 1100px é o contrário
7. **Screenshot** em 390px e 1440px (e em qualquer largura com falha)

## Checks especiais

- **Interação do hambúrguer (390px):** abre (`aria-expanded=true`,
  `nav` flex, `body.menu-aberto`), fecha com **Escape** — PASSOU
- **Menu por teclado (390px):** Tab chega ao *skip-link* visível; Tab chega ao
  botão do menu com **outline 3px**; Enter abre; Tab entra nos links do menu;
  Escape fecha e **devolve o foco** ao botão; Enter em um link navega e o menu
  volta ao estado fechado — 7/7 PASSARAM
- **Sem JavaScript:** com JS desativado o hambúrguer some e a navegação
  permanece visível (progressive enhancement) — PASSOU
- **`prefers-reduced-motion: reduce`:** nenhuma animação ativa — PASSOU

## Auditoria de acessibilidade/visual (11 páginas-chave)

| Verificação | Resultado |
|---|---|
| Contraste de texto (WCAG AA, 4.5:1 normal / 3:1 grande) | 0 problemas |
| Imagens deformadas (aspect ratio sem `object-fit` adequado) | 0 problemas |
| Fonte de inputs ≥ 16px no mobile (evita zoom automático do iOS) | 0 problemas |
| Alvos de toque ≥ 24px no mobile (WCAG 2.5.8) | 0 problemas (2 links dentro de frase, exentos pela regra "inline") |

Ajustes feitos a partir desta auditoria: `"Vivo"` do logo, logo do rodapé, logo
das telas de autenticação e os números da seção "Como funciona" passaram de
`--blood #b3121e` (2,5–2,7:1 sobre o fundo escuro) para
`--color-red-text #ff6670` (≈6,7:1); links do rodapé, `.contact-link`, nomes
de artista, links de tabela do painel e links de voltar ganharam altura mínima
de 24–28px.

## Reprodução

- Suíte responsiva: `resp-test.js` (Puppeteer, 10 larguras × 19 páginas)
- Auditoria a11y/visual: `a11y-audit.js` (contraste, imagens, alvos, teclado)
- Ambos em `C:\Users\Marilda\AppData\Local\Temp\opencode\ev-test\`, com
  relatórios (`relatorio.md`, `relatorio-a11y.md`) e screenshots (`shots/`)
- Contas de teste locais (banco `eventovivo` desta máquina): usuários 1, 4 e 6
  (`lucas.martins@email.com`, `contato@eventossul.com`,
  `vinidarosa.for@gmail.com`) com senha local definida na sessão de testes
  `EventoVivo#2026`; para rever: rodar `set-test-passwords.php` com o PHP 5.3
