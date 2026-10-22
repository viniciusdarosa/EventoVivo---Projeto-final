# EventoVivo — Banco de dados

## Instalação em banco novo (vazio)

Execute, nesta ordem, pelo cliente MySQL (phpMyAdmin, linha de
comando etc.):

| Ordem | Arquivo | O que faz |
|-------|---------|-----------|
| 1 | `instalacao/00_criar_banco.sql` | Cria o banco `eventovivo` (se não existir) e o seleciona. |
| 2 | `instalacao/01_esquema.sql` | Cria as 11 tabelas na ordem correta das chaves estrangeiras. Usa `CREATE TABLE IF NOT EXISTS`: **nunca apaga** tabelas existentes. |
| 3 | `instalacao/02_dados_demonstracao.sql` | **Opcional.** Popula categorias, usuários (inclusive um admin), eventos, artistas, avaliações etc. Só para banco vazio recém-criado — os INSERTs usam IDs fixos e falham com erro se já houver dados (de propósito: nada é sobrescrito). |

Contas de demonstração (senha `123456`; no banco ela já é gravada
como hash bcrypt `$2y$` — nenhuma senha fica em texto puro):

- `lucas.martins@email.com`, `ana.souza@email.com`,
  `marcos.henrique@email.com`, `gabriel.oliveira@email.com` — usuários
- `contato@eventossul.com` — empresa (dona dos eventos de demo)
- `admin@eventovivo.com` — **admin** (abre o PainelAdmin.php)

## Banco que já existe com dados reais

**Não** rode `00`/`01`/`02` — para bancos existentes existe a migração:

| Arquivo | O que faz |
|---------|-----------|
| `instalacao/03_migracao_banco_existente.sql` | Idempotente (pode rodar mais de uma vez; cada passo só age se faltar). Cria `carrossel_fotos` e `acesso_sistema` se não existirem; adiciona `eventos.arquivado`; torna `data_nascimento`/`cidade`/`estado` opcionais (NULL) em `usuario`; remove avaliações e perfis de freelancer duplicados; limpa linhas órfãs; cria chaves estrangeiras que faltarem; cria os índices e UNIQUEs novos. **Não apaga dados reais** — só resíduos (órfãos/duplicatas) e estrutura nova. |

Após rodar a migração, atualize os arquivos PHP do projeto (as
páginas passaram a filtrar `arquivado = 0` e a "exclusão" de evento
agora arquiva em vez de apagar).

## O que mudou em relação aos dumps antigos

Os arquivos originais foram movidos para `dumps antigos/` e **não devem
ser usados** em bancos com dados (todos contêm `DROP TABLE IF EXISTS`).
Mudanças principais:

1. **`carrossel_fotos` garantida.** O dump antigo dessa tabela era de
   outra exportação (cliente MySQL 5.5) e ficava fora do conjunto de
   instalação; instalações sem ela quebravam o carrossel do perfil com
   Fatal em `bind_param()` (`funcoes_freelancers.php` →
   `buscar_carrossel_freelancer()`). Agora o esquema novo e a migração
   sempre a criam. Todos os `prepare()` do código também passaram a
   tratar `false` (guards), então falha de tabela ausente não derruba
   mais a página.
2. **Dados de demo consistentes.** O antigo `eventovivo_freelancers.sql`
   inseria um freelancer "Baixista" com `usuario_id = 6`, mas o dump de
   usuários só ia até o 5 — ao importar o conjunto, o freelancer ficava
   órfão (os dumps desativam `FOREIGN_KEY_CHECKS`). Os dados novos
   (`02_dados_demonstracao.sql`) removem essa linha de teste e criam um
   usuário admin (id 6) para o painel.
3. **Evento "excluído" agora é arquivado.** Nova coluna
   `eventos.arquivado` (0/1). A exclusão na área do usuário marca
   `arquivado = 1` (pode ser restaurado); as listagens públicas filtram
   `arquivado = 0`. A limpeza automática que **apagava** eventos
   encerrados há mais de 1 mês na abertura da página pública passou a
   apenas marcá-los como arquivados.
4. **Avaliação duplicada bloqueada no banco:**
   `UNIQUE uq_avaliacao_usuario_freelancer (avaliador, freelancer_id)`.
5. **Um perfil de artista por conta:** `UNIQUE uq_freelancer_usuario
   (usuario_id)` em `freelancers`.
6. **Índices novos em `eventos`:** `idx_evento_arquivado`,
   `idx_evento_data_inicio`, `idx_evento_data_fim`.
7. **Campos opcionais do cadastro agora aceitam NULL**
   (`usuario.data_nascimento`, `usuario.cidade`, `usuario.estado`).
   O Cadastro.php também deixa de gravar string vazia em `cpf`
   (NULL — o `UNIQUE` de CPF aceita vários NULLs, mas colidiria com
   string vazia repetida).
8. **`acesso_sistema`** (log de acessos do painel) agora é parte do
   conjunto padrão; sem chave estrangeira de propósito (registrar
   acesso não pode falhar por conta removida).

## Estrutura (11 tabelas, ordem de criação)

1. `categorias_eventos` — categorias de eventos
2. `categorias_servicos` — categorias de serviços (artistas)
3. `usuario` — contas (usuario/empresa/admin)
4. `eventos` — eventos publicados (+ `arquivado`)
5. `freelancers` — perfis de artistas (1 por usuário)
6. `carrossel_fotos` — portfólio do artista
7. `avaliacoes` — 1 avaliação por (usuário, artista)
8. `favoritos` — favoritos de evento ou artista
9. `mensagens` — mensagens entre usuários
10. `notificacoes` — notificações
11. `acesso_sistema` — log de acessos (sem FK)
