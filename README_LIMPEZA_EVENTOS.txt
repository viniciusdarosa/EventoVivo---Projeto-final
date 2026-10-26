ARQUIVAMENTO DE EVENTOS (SEM EXCLUSAO AUTOMATICA)

CORRECAO DE COMPORTAMENTO:
Antes, Telas/Eventos.php chamada arquivar_eventos_encerrados() na abertura
da listagem — ou seja, qualquer pessoa acessando a pagina podia alterar
dados do banco (arquivava eventos encerrados ha mais de 1 mes). Essa
rotina foi REMOVIDA de Telas/Componentes/funcoes_eventos.php.
Hoje a listagem e somente leitura: navegar nunca modifica nada.

COMO FUNCIONA AGORA:
- O arquivamento e uma acao EXPLICITA do dono do evento: botao
  "Arquivar" em Telas/CRUD_Eventos.php e nos cards (Telas/ExcluirEvento.php,
  sempre POST + token CSRF, apenas para o proprio usuario logado).
- NADA e apagado: o registro e a imagem de capa permanecem no banco e no
  servidor. Eventos arquivados somem das listagens publicas (Eventos,
  Busca, Home, Meus Favoritos e painel admin filtram arquivado = 0) e
  podem ser restaurados pelo dono em Telas/CRUD_Eventos.php
  (botao "Restaurar" em Telas/RestaurarEvento.php).
- A exclusao definitiva de imagem so acontece junto com a atualizacao do
  evento (Troca de capa em Telas/EditarEvento.php) ou quando o perfil de
  artista e excluido — nunca por navegacao.

A coluna arquivado exige a migracao
banco de dados/instalacao/03_migracao_banco_existente.sql em bancos que
ja existem.
