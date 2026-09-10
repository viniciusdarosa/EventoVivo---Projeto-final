LIMPEZA AUTOMATICA DE EVENTOS

A página Telas/Eventos.php agora chama a função limpar_eventos_antigos() antes de carregar a listagem.
A função está em Telas/Componentes/funcoes_eventos.php e remove eventos cuja data_fim_evento seja anterior a 1 mês atrás, além de apagar a imagem de capa associada.

Importante: como esta solução é disparada pela abertura de Eventos.php, a limpeza ocorre quando alguém acessar a página.
