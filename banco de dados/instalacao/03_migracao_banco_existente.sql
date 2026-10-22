-- ============================================================
-- EventoVivo — 03_migracao_banco_existente.sql
-- Ajusta um banco que JÁ TEM DADOS REAIS para a estrutura nova.
-- Todas as mudanças são protegidas por verificações no
-- information_schema (idempotente: rode quantas vezes quiser;
-- nada que já existe é apagado ou recriado).
--
-- O que este script faz:
--   1) Cria, se faltarem, as tabelas carrossel_fotos e
--      acesso_sistema (instalações antigas não as tinham).
--   2) Adiciona eventos.arquivado (arquivamento de eventos).
--   3) Torna opcionais (NULL) data_nascimento, cidade e estado
--      em usuario, como no formulário de cadastro.
--   4) Remove avaliações duplicadas do mesmo usuário para o
--      mesmo artista, e perfis de freelancer duplicados.
--   5) Remove linhas órfãs (sem usuário/categoria correspondente).
--   6) Cria, se faltarem, as chaves estrangeiras.
--   7) Cria os índices e restrições UNIQUE novos.
--
-- NÃO execute em um banco vazio — para banco novo use
-- 00_criar_banco.sql + 01_esquema.sql (+ demo opcional).
-- ============================================================

USE `eventovivo`;

-- ============================================================
-- 1) Tabelas que podiam faltar em instalações antigas
-- ============================================================
CREATE TABLE IF NOT EXISTS `carrossel_fotos` (
  `id_foto` int(11) NOT NULL AUTO_INCREMENT,
  `freelancer_id` int(11) NOT NULL,
  `imagem` varchar(255) NOT NULL,
  `legenda` varchar(255) DEFAULT NULL,
  `data_cadastro` datetime DEFAULT NULL,
  PRIMARY KEY (`id_foto`),
  KEY `idx_carrossel_fre_data` (`freelancer_id`,`data_cadastro`,`id_foto`),
  CONSTRAINT `fk_carrossel_freelancer` FOREIGN KEY (`freelancer_id`) REFERENCES `freelancers` (`id_freelancer`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `acesso_sistema` (
  `id_acesso` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `sessao` varchar(90) DEFAULT NULL,
  `pagina` varchar(200) DEFAULT NULL,
  `endereco_ip` varchar(45) DEFAULT NULL,
  `data_acesso` datetime NOT NULL,
  PRIMARY KEY (`id_acesso`),
  KEY `idx_acesso_data` (`data_acesso`),
  KEY `idx_acesso_usuario` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2) Coluna nova: eventos.arquivado
-- ============================================================
SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'eventos'
                  AND COLUMN_NAME = 'arquivado');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `eventos` ADD COLUMN `arquivado` tinyint(1) NOT NULL DEFAULT ''0'' AFTER `data_publicacao`',
    'SELECT ''eventos.arquivado já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

-- ============================================================
-- 3) Campos opcionais do cadastro passam a aceitar NULL
--    (apenas se ainda forem NOT NULL)
-- ============================================================
SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'usuario'
                  AND COLUMN_NAME = 'data_nascimento'
                  AND IS_NULLABLE = 'NO');
SET @sql := IF(@existe > 0,
    'ALTER TABLE `usuario` MODIFY COLUMN `data_nascimento` date DEFAULT NULL',
    'SELECT ''usuario.data_nascimento já aceita NULL'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'usuario'
                  AND COLUMN_NAME = 'cidade'
                  AND IS_NULLABLE = 'NO');
SET @sql := IF(@existe > 0,
    'ALTER TABLE `usuario` MODIFY COLUMN `cidade` varchar(100) DEFAULT NULL',
    'SELECT ''usuario.cidade já aceita NULL'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'usuario'
                  AND COLUMN_NAME = 'estado'
                  AND IS_NULLABLE = 'NO');
SET @sql := IF(@existe > 0,
    'ALTER TABLE `usuario` MODIFY COLUMN `estado` char(2) DEFAULT NULL',
    'SELECT ''usuario.estado já aceita NULL'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

-- ============================================================
-- 4) Limpeza de duplicatas antes das restrições UNIQUE
-- ============================================================

-- Avaliações: mantém apenas o registro mais antigo de cada par
-- (avaliador, freelancer_id).
DELETE a1 FROM `avaliacoes` a1
INNER JOIN `avaliacoes` a2
   ON a1.avaliador = a2.avaliador
  AND a1.freelancer_id = a2.freelancer_id
  AND a1.id > a2.id;

-- Freelancers: mantém apenas o perfil mais antigo de cada usuário.
DELETE f1 FROM `freelancers` f1
INNER JOIN `freelancers` f2
   ON f1.usuario_id = f2.usuario_id
  AND f1.id_freelancer > f2.id_freelancer;

-- ============================================================
-- 5) Remoção de linhas órfãs (sem correspondência nas tabelas
--    pai). Em bancos com as FKs já ativas estas consultas não
--    retornam nada; em bancos montados a partir de dumps com
--    FOREIGN_KEY_CHECKS=0 elas limpariam os resíduos.
-- ============================================================
DELETE FROM `avaliacoes`
 WHERE NOT EXISTS (SELECT 1 FROM `usuario` u WHERE u.id_usuario = `avaliacoes`.avaliador)
    OR NOT EXISTS (SELECT 1 FROM `freelancers` f WHERE f.id_freelancer = `avaliacoes`.freelancer_id);

DELETE FROM `favoritos`
 WHERE NOT EXISTS (SELECT 1 FROM `usuario` u WHERE u.id_usuario = `favoritos`.usuario_id)
    OR (`evento_id` IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `eventos` e WHERE e.id_evento = `favoritos`.evento_id))
    OR (`freelancer_id` IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `freelancers` f WHERE f.id_freelancer = `favoritos`.freelancer_id));

DELETE FROM `mensagens`
 WHERE NOT EXISTS (SELECT 1 FROM `usuario` u WHERE u.id_usuario = `mensagens`.remetente)
    OR NOT EXISTS (SELECT 1 FROM `usuario` u WHERE u.id_usuario = `mensagens`.destinatario);

DELETE FROM `notificacoes`
 WHERE NOT EXISTS (SELECT 1 FROM `usuario` u WHERE u.id_usuario = `notificacoes`.usuario_remetente)
    OR NOT EXISTS (SELECT 1 FROM `usuario` u WHERE u.id_usuario = `notificacoes`.usuario_destinatario);

DELETE FROM `carrossel_fotos`
 WHERE NOT EXISTS (SELECT 1 FROM `freelancers` f WHERE f.id_freelancer = `carrossel_fotos`.freelancer_id);

DELETE FROM `eventos`
 WHERE NOT EXISTS (SELECT 1 FROM `usuario` u WHERE u.id_usuario = `eventos`.usuario_id)
    OR NOT EXISTS (SELECT 1 FROM `categorias_eventos` c WHERE c.id_categoria = `eventos`.categoria_id);

DELETE FROM `freelancers`
 WHERE NOT EXISTS (SELECT 1 FROM `usuario` u WHERE u.id_usuario = `freelancers`.usuario_id)
    OR NOT EXISTS (SELECT 1 FROM `categorias_servicos` c WHERE c.id_categoria = `freelancers`.categoria_id);

-- ============================================================
-- 6) Chaves estrangeiras (apenas se ainda não existirem)
-- ============================================================
SET @existe := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'eventos'
                  AND CONSTRAINT_NAME = 'fk_evento_usuario'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `eventos` ADD CONSTRAINT `fk_evento_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT ''fk_evento_usuario já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

SET @existe := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'eventos'
                  AND CONSTRAINT_NAME = 'fk_evento_categoria'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `eventos` ADD CONSTRAINT `fk_evento_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_eventos` (`id_categoria`) ON UPDATE CASCADE',
    'SELECT ''fk_evento_categoria já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

SET @existe := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'freelancers'
                  AND CONSTRAINT_NAME = 'fk_freelancer_usuario'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `freelancers` ADD CONSTRAINT `fk_freelancer_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT ''fk_freelancer_usuario já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

SET @existe := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'freelancers'
                  AND CONSTRAINT_NAME = 'fk_freelancer_categoria'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `freelancers` ADD CONSTRAINT `fk_freelancer_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_servicos` (`id_categoria`) ON UPDATE CASCADE',
    'SELECT ''fk_freelancer_categoria já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

SET @existe := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'avaliacoes'
                  AND CONSTRAINT_NAME = 'fk_avaliacao_usuario'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `avaliacoes` ADD CONSTRAINT `fk_avaliacao_usuario` FOREIGN KEY (`avaliador`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT ''fk_avaliacao_usuario já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

SET @existe := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'avaliacoes'
                  AND CONSTRAINT_NAME = 'fk_avaliacao_freelancer'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `avaliacoes` ADD CONSTRAINT `fk_avaliacao_freelancer` FOREIGN KEY (`freelancer_id`) REFERENCES `freelancers` (`id_freelancer`) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT ''fk_avaliacao_freelancer já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

SET @existe := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'carrossel_fotos'
                  AND CONSTRAINT_NAME = 'fk_carrossel_freelancer'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `carrossel_fotos` ADD CONSTRAINT `fk_carrossel_freelancer` FOREIGN KEY (`freelancer_id`) REFERENCES `freelancers` (`id_freelancer`) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT ''fk_carrossel_freelancer já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

SET @existe := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'favoritos'
                  AND CONSTRAINT_NAME = 'fk_favorito_usuario'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `favoritos` ADD CONSTRAINT `fk_favorito_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT ''fk_favorito_usuario já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

SET @existe := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'favoritos'
                  AND CONSTRAINT_NAME = 'fk_favorito_evento'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `favoritos` ADD CONSTRAINT `fk_favorito_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id_evento`) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT ''fk_favorito_evento já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

SET @existe := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'favoritos'
                  AND CONSTRAINT_NAME = 'fk_favorito_freelancer'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `favoritos` ADD CONSTRAINT `fk_favorito_freelancer` FOREIGN KEY (`freelancer_id`) REFERENCES `freelancers` (`id_freelancer`) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT ''fk_favorito_freelancer já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

SET @existe := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'mensagens'
                  AND CONSTRAINT_NAME = 'fk_mensagem_remetente'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `mensagens` ADD CONSTRAINT `fk_mensagem_remetente` FOREIGN KEY (`remetente`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT ''fk_mensagem_remetente já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

SET @existe := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'mensagens'
                  AND CONSTRAINT_NAME = 'fk_mensagem_destinatario'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `mensagens` ADD CONSTRAINT `fk_mensagem_destinatario` FOREIGN KEY (`destinatario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT ''fk_mensagem_destinatario já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

SET @existe := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'notificacoes'
                  AND CONSTRAINT_NAME = 'fk_notificacao_remetente'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `notificacoes` ADD CONSTRAINT `fk_notificacao_remetente` FOREIGN KEY (`usuario_remetente`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT ''fk_notificacao_remetente já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

SET @existe := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'notificacoes'
                  AND CONSTRAINT_NAME = 'fk_notificacao_destinatario'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `notificacoes` ADD CONSTRAINT `fk_notificacao_destinatario` FOREIGN KEY (`usuario_destinatario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT ''fk_notificacao_destinatario já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

-- ============================================================
-- 7) Índices e restrições UNIQUE novos
-- ============================================================

-- Impede o mesmo usuário de avaliar o mesmo artista duas vezes.
SET @existe := (SELECT COUNT(*) FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'avaliacoes'
                  AND INDEX_NAME = 'uq_avaliacao_usuario_freelancer');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `avaliacoes` ADD UNIQUE KEY `uq_avaliacao_usuario_freelancer` (`avaliador`,`freelancer_id`)',
    'SELECT ''uq_avaliacao_usuario_freelancer já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

-- Um único perfil de artista por conta de usuário.
SET @existe := (SELECT COUNT(*) FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'freelancers'
                  AND INDEX_NAME = 'uq_freelancer_usuario');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `freelancers` ADD UNIQUE KEY `uq_freelancer_usuario` (`usuario_id`)',
    'SELECT ''uq_freelancer_usuario já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

-- Índices de apoio às consultas de eventos.
SET @existe := (SELECT COUNT(*) FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'eventos'
                  AND INDEX_NAME = 'idx_evento_arquivado');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `eventos` ADD INDEX `idx_evento_arquivado` (`arquivado`)',
    'SELECT ''idx_evento_arquivado já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

SET @existe := (SELECT COUNT(*) FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'eventos'
                  AND INDEX_NAME = 'idx_evento_data_inicio');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `eventos` ADD INDEX `idx_evento_data_inicio` (`data_inicio_evento`)',
    'SELECT ''idx_evento_data_inicio já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

SET @existe := (SELECT COUNT(*) FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'eventos'
                  AND INDEX_NAME = 'idx_evento_data_fim');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `eventos` ADD INDEX `idx_evento_data_fim` (`data_fim_evento`)',
    'SELECT ''idx_evento_data_fim já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

-- Índice do carrossel (a tabela pode vir de um dump antigo, sem ele).
SET @existe := (SELECT COUNT(*) FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'carrossel_fotos'
                  AND INDEX_NAME = 'idx_carrossel_fre_data');
SET @sql := IF(@existe = 0,
    'ALTER TABLE `carrossel_fotos` ADD INDEX `idx_carrossel_fre_data` (`freelancer_id`,`data_cadastro`,`id_foto`)',
    'SELECT ''idx_carrossel_fre_data já existe'' AS info');
PREPARE mig FROM @sql; EXECUTE mig; DEALLOCATE PREPARE mig;

-- ============================================================
-- Fim da migração.
-- ============================================================
SELECT 'Migração do EventoVivo concluída.' AS resultado;
