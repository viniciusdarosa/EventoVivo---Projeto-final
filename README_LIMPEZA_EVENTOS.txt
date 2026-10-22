ARQUIVAMENTO AUTOMATICO DE EVENTOS

A pagina Telas/Eventos.php agora chama a funcao arquivar_eventos_encerrados()
antes de carregar a listagem. A funcao esta em Telas/Componentes/funcoes_eventos.php
e apenas MARCA como arquivados (eventos.arquivado = 1) os eventos cuja
data_fim_evento seja anterior a 1 mes atras.

NADA e apagado: o registro e a imagem de capa permanecem no banco e no
servidor. Eventos arquivados somem das listagens publicas (Eventos, Busca,
Home e painel admin filtram arquivado = 0) e podem ser restaurados pelo
dono em Telas/CRUD_Eventos.php (botao "Restaurar").

O botao "Excluir" da area do usuario (CRUD_Eventos, Busca, cards) tambem
arquiva em vez de apagar — ver Telas/ExcluirEvento.php e
Telas/RestaurarEvento.php.

Importante: como a rotina e disparada pela abertura de Eventos.php, o
arquivamento ocorre quando alguem acessa a pagina. A coluna arquivado
exige a migracao banco de dados/instalacao/03_migracao_banco_existente.sql
em bancos que ja existem.
