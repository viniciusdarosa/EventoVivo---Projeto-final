-- =========================================================
-- EventoVivo — tabelas do sistema
-- Rode este script no banco "eventovivo" (mesmo banco usado
-- em config/conexao.php).
-- =========================================================

-- Tabela de usuários (referenciada por eventos.usuario_id)
CREATE TABLE IF NOT EXISTS usuario (
  id_usuario INT(11) NOT NULL AUTO_INCREMENT,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL,
  senha VARCHAR(255) NOT NULL,
  salt VARCHAR(64) NOT NULL,
  telefone VARCHAR(20) DEFAULT NULL,
  cpf VARCHAR(14) DEFAULT NULL,
  data_nascimento DATE DEFAULT NULL,
  foto_perfil VARCHAR(255) DEFAULT NULL,
  biografia TEXT,
  cidade VARCHAR(100) DEFAULT NULL,
  estado CHAR(2) DEFAULT NULL,
  cep VARCHAR(9) DEFAULT NULL,
  endereco VARCHAR(150) DEFAULT NULL,
  numero VARCHAR(10) DEFAULT NULL,
  complemento VARCHAR(100) DEFAULT NULL,
  tipo ENUM('usuario','empresa','admin') NOT NULL DEFAULT 'usuario',
  data_cadastro DATETIME NOT NULL,
  PRIMARY KEY (id_usuario),
  UNIQUE KEY uk_usuario_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- Categorias de eventos
-- =========================================================
CREATE TABLE IF NOT EXISTS categorias_eventos (
  id_categoria INT(11) NOT NULL AUTO_INCREMENT,
  nome VARCHAR(100) NOT NULL,
  descricao VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categorias iniciais (ajuste/complete como quiser).
INSERT INTO categorias_eventos (nome, descricao) VALUES
  ('Música', 'Shows, festivais e apresentações musicais'),
  ('Teatro', 'Peças e espetáculos teatrais'),
  ('Dança', 'Espetáculos e apresentações de dança'),
  ('Cultura', 'Feiras, exposições e eventos culturais em geral'),
  ('Outros', 'Demais tipos de evento');

CREATE TABLE IF NOT EXISTS eventos (
  id_evento INT(11) NOT NULL AUTO_INCREMENT,
  usuario_id INT(11) NOT NULL,
  categoria_id INT(11) NOT NULL,
  titulo VARCHAR(200) NOT NULL,
  descricao TEXT,
  data_inicio_evento DATE NOT NULL,
  data_fim_evento DATE NOT NULL,
  hora_inicio TIME NOT NULL,
  hora_fim TIME NOT NULL,
  endereco VARCHAR(150) NOT NULL,
  numero VARCHAR(10) DEFAULT NULL,
  cidade VARCHAR(100) NOT NULL,
  estado CHAR(2) NOT NULL,
  cep VARCHAR(9) DEFAULT NULL,
  valor DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  vagas INT(11) NOT NULL DEFAULT 0,
  imagem_capa VARCHAR(255) DEFAULT NULL,
  faixa_etaria ENUM('Livre','10','12','14','16','18+') NOT NULL DEFAULT 'Livre',
  data_publicacao DATETIME NOT NULL,
  PRIMARY KEY (id_evento),
  KEY fk_eventos_categoria (categoria_id),
  KEY fk_eventos_usuario (usuario_id),
  CONSTRAINT fk_eventos_categoria FOREIGN KEY (categoria_id)
    REFERENCES categorias_eventos (id_categoria),
  CONSTRAINT fk_eventos_usuario FOREIGN KEY (usuario_id)
    REFERENCES usuario (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
