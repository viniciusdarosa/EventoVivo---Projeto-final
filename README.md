# EventoVivo

**A cena independente tem endereço.** O EventoVivo é uma plataforma web que aproxima artistas independentes, profissionais freelancers, produtores de eventos e pessoas interessadas em cultura. O projeto foi desenvolvido como trabalho final do curso de Informática da SATC.

Artistas podem apresentar seus trabalhos e serem descobertos; organizadores podem divulgar eventos; e o público pode explorar o que acontece na sua região.

## O que você encontra

- **Busca e descoberta:** navegue por artistas, freelancers e eventos culturais, com busca geral pelo cabeçalho e filtros por categoria, período, cidade e estado.
- **Perfis profissionais:** divulgue descrição, categoria de serviço, experiência, portfólio, redes sociais e avaliações.
- **Eventos:** consulte informações como data, local, categoria, preço e classificação etária. Publicar eventos é restrito a contas de organizador (tipo `empresa` ou `admin`), regra validada também no servidor; contas do tipo `usuario` podem gerenciar eventos legados próprios.
- **Favoritos:** salve eventos e perfis de artista com o botão ☆ Salvar e acompanhe tudo em **Meus Favoritos**.
- **Contatos diretos:** cards de evento oferecem "Contatar organizador" (WhatsApp/e-mail) e a busca lista contatos reais dos artistas.
- **Contas de usuário:** cadastro (com validação de CPF e telefone) e login para acessar as áreas de gerenciamento.
- **Área administrativa:** acompanhe indicadores de acesso, eventos e artistas (visível apenas para contas `admin`).
- **Interface responsiva:** páginas feitas em HTML, CSS e JavaScript, com conteúdo dinâmico em PHP.

## Tecnologias

- PHP e MySQL
- MySQL
- HTML, CSS e JavaScript
- Apache (o projeto foi desenvolvido e organizado para o ambiente EasyPHP)

## Como executar localmente

1. Instale um ambiente local com Apache, PHP e MySQL. O projeto foi desenvolvido com EasyPHP 5.3.9.
2. Copie ou clone o repositório para a pasta pública do servidor web.
3. Crie um banco de dados chamado `eventovivo` com codificação `utf8mb4`.
4. Importe os arquivos SQL da pasta [`banco de dados/`](banco%20de%20dados/) nesta ordem, para respeitar as dependências entre tabelas:

   1. `eventovivo_usuario.sql`
   2. `eventovivo_categorias_servicos.sql` e `eventovivo_categorias_eventos.sql`
   3. `eventovivo_freelancers.sql` e `eventovivo_eventos.sql`
   4. `eventovivo_avaliacoes.sql`, `eventovivo_carrossel_fotos.sql` e `eventovivo_favoritos.sql`
   5. `eventovivo_mensagens.sql`, `eventovivo_notificacoes.sql` e `eventovivo_acesso_sistema.sql`

5. Confira as credenciais de conexão em [`config/conexao.php`](config/conexao.php) e ajuste-as ao seu MySQL local.
6. Inicie Apache e MySQL e abra `Telas/Home.php` pelo endereço local do servidor, por exemplo `http://localhost/EventoVivo---Projeto-final/Telas/Home.php`.

> **Atenção:** os arquivos SQL são dumps de desenvolvimento e alguns recriam tabelas existentes. Importe-os somente em um banco local descartável ou faça backup antes. Eles também incluem registros demonstrativos. Revise e remova esses dados e configure credenciais próprias antes de publicar uma instância do sistema.

## Estrutura do projeto

```text
├── banco de dados/   # Estrutura e dados demonstrativos do MySQL
├── config/           # Conexão e inicialização da aplicação
├── Css/              # Estilos das páginas
├── Telas/            # Páginas e componentes PHP
├── uploads/          # Imagens de perfis, portfólios e eventos
└── logs/             # Registros da aplicação
```

## Sobre o projeto

O EventoVivo foi criado para um projeto acadêmico e demonstra um fluxo completo de descoberta e divulgação da cena cultural independente, com páginas públicas e recursos de gerenciamento conectados a um banco relacional.

## Funcionalidades futuras

As tabelas `mensagens` e `notificacoes` já existem no banco (com dados de demonstração), mas as interfaces de envio/leitura de mensagens e de central de notificações **ainda não foram implementadas** — por isso não há botões ou contadores delas na interface. O plano previsto é:

- **Mensagens privadas** entre público/organizador e artistas (envio, caixa de entrada e leitura).
- **Notificações** geradas por eventos relevantes para o usuário (ex.: avaliação recebida, favoritos novos) com central de leitura.

Enquanto isso, o contato acontece pelos canais já prontos: WhatsApp, e-mail e Instagram nos perfis e cards.

## Licença

Este projeto está disponível sob a licença [MIT](LICENSE).

---

Desenvolvido por **Vinícius Cardoso da Rosa e Nicolas Aravena Rocha** · Projeto final do curso de Informática da SATC.
