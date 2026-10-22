-- ============================================================
-- EventoVivo — 02_dados_demonstracao.sql
-- DADOS DE DEMONSTRAÇÃO (OPCIONAL) — banco VAZIO recém-criado
-- com 00_criar_banco.sql + 01_esquema.sql.
--
-- NÃO execute este arquivo em um banco que já tem dados reais:
-- os INSERTs usam IDs fixos e falham com erro de chave primária
-- (o que é proposital: nada é apagado nem sobrescrito).
--
-- Contas de demonstração (senha "123456"; armazenada já como hash
-- bcrypt $2y$ — nenhuma senha fica em texto puro no banco):
--   1 lucas.martins@email.com   (usuário / músico)
--   2 ana.souza@email.com       (usuário / cantora)
--   3 marcos.henrique@email.com (usuário / fotógrafo)
--   4 contato@eventossul.com    (empresa / produtora de eventos)
--   5 gabriel.oliveira@email.com (usuário / dançarino)
--   6 admin@eventovivo.com      (ADMIN — painel administrativo)
--
-- Correção de consistência: o freelancer "Baixista" que apontava
-- para um usuario_id sem usuário correspondente foi removido
-- destes dados. Os 5 freelancers abaixo referenciam apenas
-- usuários existentes (1, 2, 3, 5 e 4).
-- ============================================================

USE `eventovivo`;

-- ---- Categorias de eventos ----
INSERT INTO `categorias_eventos` (`id_categoria`, `nome`, `descricao`) VALUES
  (1, 'Shows e Música', 'Eventos musicais, apresentações de bandas, cantores, DJs e músicos.'),
  (2, 'Teatro', 'Peças teatrais, apresentações cênicas e espetáculos.'),
  (3, 'Dança', 'Apresentações, competições e espetáculos de dança.'),
  (4, 'Cultura', 'Eventos culturais, exposições, feiras e manifestações artísticas.'),
  (5, 'Festivais', 'Festivais de música, cultura, gastronomia e entretenimento.');

-- ---- Categorias de serviços ----
INSERT INTO `categorias_servicos` (`id_categoria`, `nome`, `descricao`) VALUES
  (1, 'Música', 'Cantores, bandas, DJs e instrumentistas.'),
  (2, 'Fotografia', 'Fotógrafos especializados em eventos e ensaios.'),
  (3, 'Vídeo', 'Profissionais de filmagem, edição e produção audiovisual.'),
  (4, 'Dança', 'Dançarinos, coreógrafos e grupos de dança.'),
  (5, 'Design', 'Designers gráficos especializados em divulgação e identidade visual.');

-- ---- Usuários (1 a 5 de demonstração + 6 admin) ----
INSERT INTO `usuario` (`id_usuario`, `nome`, `email`, `senha`, `telefone`, `cpf`, `data_nascimento`, `foto_perfil`, `biografia`, `cidade`, `estado`, `cep`, `endereco`, `numero`, `complemento`, `tipo`) VALUES
  (1, 'Lucas Martins', 'lucas.martins@email.com', '$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC', '(48) 99911-2233', '123.456.789-09', '2005-03-15', 'lucas.jpg', 'Músico e produtor interessado em eventos culturais.', 'Criciúma', 'SC', '88800-000', 'Rua das Flores', '120', NULL, 'usuario'),
  (2, 'Ana Beatriz Souza', 'ana.souza@email.com', '$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC', '(48) 99822-3344', '234.567.890-92', '1998-07-21', 'ana.jpg', 'Cantora e compositora especializada em música brasileira.', 'Criciúma', 'SC', '88801-000', 'Rua João Pessoa', '245', 'Apto 302', 'usuario'),
  (3, 'Marcos Henrique', 'marcos.henrique@email.com', '$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC', '(48) 99733-4455', '345.678.901-75', '1995-11-08', 'marcos.jpg', 'Fotógrafo profissional especializado em eventos.', 'Içara', 'SC', '88820-000', 'Avenida Central', '450', NULL, 'usuario'),
  (4, 'Eventos Sul Produções', 'contato@eventossul.com', '$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC', '(48) 99644-5566', '456.789.012-49', '1990-05-10', 'eventossul.jpg', 'Empresa especializada na produção de eventos culturais.', 'Criciúma', 'SC', '88802-000', 'Rua Coronel Pedro Benedet', '800', NULL, 'empresa'),
  (5, 'Gabriel Oliveira', 'gabriel.oliveira@email.com', '$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC', '(48) 99555-6677', '567.890.123-03', '2000-09-18', 'gabriel.jpg', 'Dançarino e profissional de apresentações artísticas.', 'Criciúma', 'SC', '88803-000', 'Rua Marechal Deodoro', '100', NULL, 'usuario'),
  (6, 'Administrador Demo', 'admin@eventovivo.com', '$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC', NULL, NULL, '1990-01-01', NULL, 'Conta de demonstração do painel administrativo.', 'Criciúma', 'SC', NULL, NULL, NULL, NULL, 'admin');

-- ---- Eventos (todos da produtora, usuário 4) ----
INSERT INTO `eventos` (`id_evento`, `usuario_id`, `categoria_id`, `titulo`, `descricao`, `data_inicio_evento`, `data_fim_evento`, `hora_inicio`, `hora_fim`, `endereco`, `numero`, `cidade`, `estado`, `cep`, `valor`, `vagas`, `imagem_capa`, `faixa_etaria`, `data_publicacao`, `arquivado`) VALUES
  (1, 4, 1, 'Festival de Música EventoVivo', 'Festival reunindo artistas locais, bandas e cantores da região.', '2026-09-12', '2026-09-12', '18:00:00', '23:30:00', 'Parque das Nações', '100', 'Criciúma', 'SC', '88804-000', 25.00, 500, 'festival_musica.jpg', 'Livre', '2026-08-20 10:00:00', 0),
  (2, 4, 2, 'Noite de Teatro', 'Apresentação de artistas locais em uma noite dedicada ao teatro.', '2026-09-20', '2026-09-20', '19:30:00', '22:00:00', 'Teatro Municipal', '200', 'Criciúma', 'SC', '88805-000', 30.00, 250, 'teatro.jpg', '12', '2026-08-20 10:30:00', 0),
  (3, 4, 3, 'Festival de Dança Sul', 'Evento com apresentações de grupos e dançarinos da região.', '2026-10-05', '2026-10-05', '15:00:00', '21:00:00', 'Centro Cultural', '350', 'Içara', 'SC', '88820-100', 20.00, 300, 'danca.jpg', 'Livre', '2026-08-20 11:00:00', 0),
  (4, 4, 4, 'Feira Cultural Regional', 'Feira com artistas, artesãos, apresentações musicais e exposições.', '2026-10-15', '2026-10-17', '10:00:00', '22:00:00', 'Praça Central', '50', 'Criciúma', 'SC', '88806-000', 0.00, 1000, 'feira_cultural.jpg', 'Livre', '2026-08-20 11:30:00', 0),
  (5, 4, 5, 'Festival de Primavera', 'Festival com música ao vivo, apresentações artísticas e gastronomia.', '2026-11-07', '2026-11-08', '14:00:00', '23:00:00', 'Parque Centenário', '500', 'Criciúma', 'SC', '88807-000', 35.00, 800, 'festival_primavera.jpg', 'Livre', '2026-08-20 12:00:00', 0);

-- ---- Freelancers (apenas usuarios existentes: 1, 2, 3, 5 e 4) ----
INSERT INTO `freelancers` (`id_freelancer`, `usuario_id`, `categoria_id`, `profissao`, `descricao`, `experiencia`, `valor_hora`, `rede_social`) VALUES
  (1, 1, 1, 'Músico', 'Músico especializado em apresentações ao vivo e eventos.', 'Mais de 5 anos de experiência em apresentações musicais.', 150.00, '@lucasmusic'),
  (2, 2, 1, 'Cantora', 'Cantora especializada em música brasileira e pop.', 'Mais de 5 anos realizando apresentações em bares, eventos e festivais.', 180.00, '@anabeatriz.music'),
  (3, 3, 2, 'Fotógrafo', 'Fotógrafo especializado em shows, eventos e ensaios.', 'Mais de 7 anos trabalhando com fotografia profissional.', 120.00, '@marcosfoto'),
  (4, 5, 4, 'Dançarino', 'Dançarino especializado em apresentações urbanas e coreografias.', 'Participação em grupos de dança e apresentações culturais.', 100.00, '@gabrieldanca'),
  (5, 4, 5, 'Designer Gráfico', 'Designer especializado em cartazes e identidade visual para eventos.', 'Experiência na criação de materiais gráficos para artistas e produtores.', 80.00, '@eventossuldesign');

-- ---- Fotos do carrossel de portfólio ----
INSERT INTO `carrossel_fotos` (`id_foto`, `freelancer_id`, `imagem`, `legenda`, `data_cadastro`) VALUES
  (1, 1, 'portfolio_lucas.png', 'Apresentação musical realizada em evento cultural.', '2026-10-08 00:00:01'),
  (2, 2, 'portfolio_ana.png', 'Apresentação da cantora Ana Beatriz em festival regional.', '2026-10-08 00:00:02'),
  (3, 3, 'portfolio_marcos.png', 'Fotografia profissional realizada durante um show ao vivo.', '2026-10-08 00:00:03'),
  (4, 4, 'portfolio_gabriel.png', 'Apresentação de dança urbana em evento cultural.', '2026-10-08 00:00:04'),
  (5, 5, 'portfolio_eventossul.png', 'Identidade visual criada para divulgação de festival musical.', '2026-10-08 00:00:05');

-- ---- Avaliações (uma por par avaliador/artista) ----
INSERT INTO `avaliacoes` (`id`, `avaliador`, `freelancer_id`, `nota`, `comentario`, `data_avaliacao`) VALUES
  (1, 2, 1, 5, 'Excelente músico e ótima apresentação. Recomendo.', '2026-08-15 14:30:00'),
  (2, 1, 2, 5, 'Excelente cantora e ótima interação com o público.', '2026-08-16 16:00:00'),
  (3, 4, 3, 5, 'Fotografias de ótima qualidade e entrega rápida.', '2026-08-17 10:20:00'),
  (4, 1, 4, 5, 'Excelente dançarino e muito profissional.', '2026-08-18 18:45:00'),
  (5, 4, 5, 4, 'Bom trabalho de design e ótima qualidade visual.', '2026-08-19 12:10:00');

-- ---- Favoritos ----
INSERT INTO `favoritos` (`id`, `usuario_id`, `evento_id`, `freelancer_id`) VALUES
  (1, 1, 1, NULL),
  (2, 1, NULL, 2),
  (3, 2, 5, NULL),
  (4, 3, 3, NULL),
  (5, 5, NULL, 3);

-- ---- Mensagens ----
INSERT INTO `mensagens` (`id_mensagem`, `remetente`, `destinatario`, `mensagem`, `data_envio`) VALUES
  (1, 1, 2, 'Olá Ana! Gostaria de saber sobre sua disponibilidade para um evento.', '2026-08-20 09:00:00'),
  (2, 2, 1, 'Olá Lucas! Claro, posso verificar minha agenda. Qual seria a data?', '2026-08-20 09:10:00'),
  (3, 4, 2, 'Gostaríamos de convidá-la para participar do Festival de Música EventoVivo.', '2026-08-20 10:00:00'),
  (4, 3, 4, 'Tenho interesse em trabalhar como fotógrafo no próximo evento.', '2026-08-20 10:30:00'),
  (5, 2, 4, 'Obrigada pelo contato! Tenho interesse em participar do festival.', '2026-08-20 11:00:00');

-- ---- Notificações ----
INSERT INTO `notificacoes` (`id`, `usuario_remetente`, `usuario_destinatario`, `titulo`, `lida`, `data_de_envio`) VALUES
  (1, 4, 2, 'Novo convite para evento', 0, '2026-08-20 09:30:00'),
  (2, 1, 2, 'Nova mensagem recebida', 0, '2026-08-20 10:00:00'),
  (3, 1, 2, 'Nova avaliação recebida', 1, '2026-08-20 11:00:00'),
  (4, 2, 4, 'Novo interesse em evento', 0, '2026-08-20 12:00:00'),
  (5, 4, 3, 'Novo evento publicado', 1, '2026-08-20 12:30:00');

-- A tabela acesso_sistema fica vazia: é preenchida em tempo de
-- execução pelo PainelAdmin.php a cada visita.
