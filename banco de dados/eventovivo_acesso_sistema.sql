-- ==========================================================
-- EventoVivo — tabela de acessos (base do painel administrativo)
--
-- Cada visita ao site gera um registro aqui. O PainelAdmin.php usa
-- esses dados para responder "quantas pessoas estão usando o site"
-- (online agora, ativos no período e acessos por dia).
--
-- Opcional: o PainelAdmin.php também cria a tabela automaticamente
-- (CREATE TABLE IF NOT EXISTS) caso este arquivo não seja importado.
-- ==========================================================

CREATE TABLE IF NOT EXISTS `acesso_sistema` (
  `id_acesso` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `sessao` varchar(90) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pagina` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_acesso` datetime NOT NULL,
  PRIMARY KEY (`id_acesso`),
  KEY `idx_acesso_data` (`data_acesso`),
  KEY `idx_acesso_usuario` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- Permissão de administrador
--
-- O painel só abre para contas com tipo = 'admin'. Para liberar
-- uma conta já cadastrada, troque o e-mail abaixo:
--
-- UPDATE `usuario` SET `tipo` = 'admin' WHERE `email` = 'seu@email.com';
--
-- E, para criar um administrador novo:
--
-- INSERT INTO `usuario`
--   (`nome`, `email`, `senha`, `data_nascimento`, `cidade`, `estado`, `tipo`)
-- VALUES
--   ('Administrador', 'admin@eventovivo.com', '123456', '1990-01-01', 'Criciúma', 'SC', 'admin');
--
-- A senha em texto puro é convertida em hash com salt no primeiro login.
-- ==========================================================
