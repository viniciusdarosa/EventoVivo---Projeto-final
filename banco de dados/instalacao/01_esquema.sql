-- ============================================================
-- EventoVivo — 01_esquema.sql
-- Estrutura completa do banco, em ordem segura de chaves
-- estrangeiras. Usa CREATE TABLE IF NOT EXISTS: NUNCA apaga
-- tabelas existentes — seguro para bancos com dados reais.
--
-- Execute 00_criar_banco.sql antes deste arquivo.
-- Dados de demonstração (opcional): 02_dados_demonstracao.sql.
-- Banco que já existe e precisa dos ajustes novos: use
-- 03_migracao_banco_existente.sql (NÃO este arquivo).
-- ============================================================

USE `eventovivo`;

-- 1) Categorias de eventos (sem dependências)
CREATE TABLE IF NOT EXISTS `categorias_eventos` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(80) NOT NULL,
  `descricao` text,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Categorias de serviços (sem dependências)
CREATE TABLE IF NOT EXISTS `categorias_servicos` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Usuários
-- data_nascimento, cidade e estado aceitam NULL porque o
-- formulário de cadastro os marca como opcionais.
-- cpf continua UNIQUE (vários NULLs são permitidos).
CREATE TABLE IF NOT EXISTS `usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `biografia` text,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` char(2) DEFAULT NULL,
  `cep` varchar(9) DEFAULT NULL,
  `endereco` varchar(150) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `tipo` enum('usuario','empresa','admin') NOT NULL DEFAULT 'usuario',
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Eventos
-- arquivado: eventos "excluídos" na área do usuário são apenas
-- marcados como arquivados (1) e somem das listagens públicas.
-- Índices em datas apoiam as consultas por período.
CREATE TABLE IF NOT EXISTS `eventos` (
  `id_evento` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descricao` text NOT NULL,
  `data_inicio_evento` date NOT NULL,
  `data_fim_evento` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `endereco` varchar(150) NOT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `cidade` varchar(100) NOT NULL,
  `estado` char(2) NOT NULL,
  `cep` varchar(9) DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL DEFAULT '0.00',
  `vagas` int(11) NOT NULL,
  `imagem_capa` varchar(255) DEFAULT NULL,
  `faixa_etaria` enum('Livre','10','12','14','16','18+') DEFAULT 'Livre',
  `data_publicacao` datetime DEFAULT NULL,
  `arquivado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_evento`),
  KEY `fk_evento_usuario` (`usuario_id`),
  KEY `fk_evento_categoria` (`categoria_id`),
  KEY `idx_evento_arquivado` (`arquivado`),
  KEY `idx_evento_data_inicio` (`data_inicio_evento`),
  KEY `idx_evento_data_fim` (`data_fim_evento`),
  CONSTRAINT `fk_evento_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_evento_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_eventos` (`id_categoria`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) Freelancers (artistas)
-- UNIQUE em usuario_id: um único perfil de artista por conta.
CREATE TABLE IF NOT EXISTS `freelancers` (
  `id_freelancer` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `profissao` varchar(100) DEFAULT NULL,
  `descricao` text,
  `experiencia` text,
  `valor_hora` decimal(10,2) DEFAULT NULL,
  `rede_social` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_freelancer`),
  UNIQUE KEY `uq_freelancer_usuario` (`usuario_id`),
  KEY `fk_freelancer_categoria` (`categoria_id`),
  CONSTRAINT `fk_freelancer_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_freelancer_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_servicos` (`id_categoria`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6) Fotos do carrossel de portfólio do freelancer
-- ATENÇÃO: instalações antigas feitas sem este arquivo não têm
-- esta tabela, e o PerfilFreelancer.php falhava ao montar o
-- carrossel (erro de prepare/bind_param no log).
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

-- 7) Avaliações
-- UNIQUE em (avaliador, freelancer_id): impede que o mesmo
-- usuário avalie o mesmo artista duas vezes (proteção no banco,
-- além da regra já existente no PHP).
CREATE TABLE IF NOT EXISTS `avaliacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `avaliador` int(11) NOT NULL,
  `freelancer_id` int(11) NOT NULL,
  `nota` int(11) NOT NULL,
  `comentario` text,
  `data_avaliacao` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_avaliacao_usuario_freelancer` (`avaliador`,`freelancer_id`),
  KEY `fk_avaliacao_freelancer` (`freelancer_id`),
  CONSTRAINT `fk_avaliacao_usuario` FOREIGN KEY (`avaliador`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_avaliacao_freelancer` FOREIGN KEY (`freelancer_id`) REFERENCES `freelancers` (`id_freelancer`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8) Favoritos (evento_id OU freelancer_id preenchido)
CREATE TABLE IF NOT EXISTS `favoritos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `evento_id` int(11) DEFAULT NULL,
  `freelancer_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_favorito_usuario` (`usuario_id`),
  KEY `fk_favorito_evento` (`evento_id`),
  KEY `fk_favorito_freelancer` (`freelancer_id`),
  CONSTRAINT `fk_favorito_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_favorito_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id_evento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_favorito_freelancer` FOREIGN KEY (`freelancer_id`) REFERENCES `freelancers` (`id_freelancer`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9) Mensagens entre usuários
CREATE TABLE IF NOT EXISTS `mensagens` (
  `id_mensagem` int(11) NOT NULL AUTO_INCREMENT,
  `remetente` int(11) NOT NULL,
  `destinatario` int(11) NOT NULL,
  `mensagem` text NOT NULL,
  `data_envio` datetime DEFAULT NULL,
  PRIMARY KEY (`id_mensagem`),
  KEY `fk_mensagem_remetente` (`remetente`),
  KEY `fk_mensagem_destinatario` (`destinatario`),
  CONSTRAINT `fk_mensagem_remetente` FOREIGN KEY (`remetente`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mensagem_destinatario` FOREIGN KEY (`destinatario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10) Notificações
CREATE TABLE IF NOT EXISTS `notificacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_remetente` int(11) NOT NULL,
  `usuario_destinatario` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `lida` tinyint(1) DEFAULT '0',
  `data_de_envio` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_notificacao_remetente` (`usuario_remetente`),
  KEY `fk_notificacao_destinatario` (`usuario_destinatario`),
  CONSTRAINT `fk_notificacao_remetente` FOREIGN KEY (`usuario_remetente`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notificacao_destinatario` FOREIGN KEY (`usuario_destinatario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11) Acessos ao sistema (base do painel administrativo)
-- Deliberadamente SEM chave estrangeira: registrar um acesso não
-- pode falhar porque a conta foi removida ou por retenção de log.
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
