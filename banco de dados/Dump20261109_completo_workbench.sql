-- DUMP COMBINADO eventovivo: executar no editor SQL do MySQL Workbench.
-- ATENCAO: os arquivos de origem contêm DROP TABLE IF EXISTS e substituem as tabelas atuais.
USE `eventovivo`;

-- ============================================================
-- Origem: eventovivo_categorias_eventos.sql
-- ============================================================
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: eventovivo
-- ------------------------------------------------------
-- Server version	5.5.20-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categorias_eventos`
--

DROP TABLE IF EXISTS `categorias_eventos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias_eventos` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias_eventos`
--

LOCK TABLES `categorias_eventos` WRITE;
/*!40000 ALTER TABLE `categorias_eventos` DISABLE KEYS */;
INSERT INTO `categorias_eventos` VALUES (1,'Shows e Música','Eventos musicais, apresentações de bandas, cantores, DJs e músicos.'),(2,'Teatro','Peças teatrais, apresentações cênicas e espetáculos.'),(3,'Dança','Apresentações, competições e espetáculos de dança.'),(4,'Cultura','Eventos culturais, exposições, feiras e manifestações artísticas.'),(5,'Festivais','Festivais de música, cultura, gastronomia e entretenimento.');
/*!40000 ALTER TABLE `categorias_eventos` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-09 20:01:18

-- ============================================================
-- Origem: eventovivo_categorias_servicos.sql
-- ============================================================
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: eventovivo
-- ------------------------------------------------------
-- Server version	5.5.20-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categorias_servicos`
--

DROP TABLE IF EXISTS `categorias_servicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias_servicos` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias_servicos`
--

LOCK TABLES `categorias_servicos` WRITE;
/*!40000 ALTER TABLE `categorias_servicos` DISABLE KEYS */;
INSERT INTO `categorias_servicos` VALUES (1,'Música','Cantores, bandas, DJs e instrumentistas.'),(2,'Fotografia','Fotógrafos especializados em eventos e ensaios.'),(3,'Vídeo','Profissionais de filmagem, edição e produção audiovisual.'),(4,'Dança','Dançarinos, coreógrafos e grupos de dança.'),(5,'Design','Designers gráficos especializados em divulgação e identidade visual.');
/*!40000 ALTER TABLE `categorias_servicos` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-09 20:01:18

-- ============================================================
-- Origem: eventovivo_usuario.sql
-- ============================================================
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: eventovivo
-- ------------------------------------------------------
-- Server version	5.5.20-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cpf` varchar(14) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `foto_perfil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `biografia` text COLLATE utf8mb4_unicode_ci,
  `cidade` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cep` varchar(9) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` enum('usuario','empresa','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'usuario',
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'Lucas Martins','lucas.martins@email.com','sha256:7c753ae38004815061433a96ef023f8dc0c8c6fb28835254b5930a4964d18171:c61937087f2e89e0a353a22ab8d881cebab1feb0daa209d577a35fd7b36d3703','(48) 99911-2233','123.456.789-01','2005-03-15','lucas.jpg','Músico e produtor interessado em eventos culturais.','Criciúma','SC','88800-000','Rua das Flores','120',NULL,'usuario'),(2,'Ana Beatriz Souza','ana.souza@email.com','sha256:b83130fe18dcae98b5f9b46a0c9a56ce1c838f0256868b93e8cbbbde33c646f7:31e00c06fdd007b83e626a8206f182414dbab8962dd32042062ebfc132fcbc7f','(48) 99822-3344','234.567.890-12','1998-07-21','ana.jpg','Cantora e compositora especializada em música brasileira.','Criciúma','SC','88801-000','Rua João Pessoa','245','Apto 302','usuario'),(3,'Marcos Henrique','marcos.henrique@email.com','123456','(48) 99733-4455','345.678.901-23','1995-11-08','marcos.avif','Fotógrafo profissional especializado em eventos.','Içara','SC','88820-000','Avenida Central','450',NULL,'usuario'),(4,'Eventos Sul Produções','contato@eventossul.com','sha256:927bc34906dad1df8411feb23658ffe3d657a4d0c6bf0b02562d1acb210cbaf5:6f50e81cbfff1530c4d3b23863fa71d75c1f7983158af6aea7e8b221d99eb245','(48) 99644-5566','456.789.012-34','1990-05-10','eventossul.jpg','Empresa especializada na produção de eventos culturais.','Criciúma','SC','88802-000','Rua Coronel Pedro Benedet','800',NULL,'empresa'),(5,'Gabriel Oliveira','gabriel.oliveira@email.com','sha256:fe979b68b085d9cfa7e6cf84054bace5f641c5ed40876e2e44e5dbea72e8317c:ff2909fe3e7f1d7531ffb7fd699ebb0380747684dc689e8d59141c80f7d257bd','(48) 99555-6677','567.890.123-45','2000-09-18','gabriel.webp','Dançarino e profissional de apresentações artísticas.','Criciúma','SC','88803-000','Rua Marechal Deodoro','100',NULL,'usuario'),(6,'Vinícius Cardoso da Rosa','vinidarosa.for@gmail.com','$2y$10$mc5khzjFN/KYzGnQyfUWaOZb.izgFXjDJG0kBJElKjZoscFl6eev6','(48) 99637-1023','097.985.149-12','2008-11-27','perfil_102eb34dedfe53efc3931ff445615ae9.png','Admin e Baterista','Criciúma','SC','88813-392','Lucas Mendes de Souza','95',NULL,'admin'),(7,'jessica card','jessica@gmail.com','sha256:d467d0f1535d26aeaaac8bb8723af1b5cc3c83c05fbedac429a4f215618abc92:521ed6c37d17eeed180bbfd8c7f72f2351c6ac61d66fa1b8bab4ff8553bfe113','','','0000-00-00',NULL,NULL,'','','','','','','usuario'),(8,'maria gava','mariavgavavelho@gmail.com','sha256:d1d05712fe1f6782622f5d6a42db169d18a24e3435b2bb00ead808e0404f98d3:64f89abdd48b8cb7b47c11c8aaa10b490b3937e0db72392675c1610b0dc5af12','48999266630','09878547981','2011-06-23',NULL,NULL,'','','88865000','','','','usuario'),(12,'Teste Runtime','teste.runtime@eventovivo.com','sha256:2c8412b32baef1d6289fbfc689d8773098f3065734f3267ec145218ad932dbff:0cf8550429ed57deb6cc6f6a9697afce3bfec45da0edeaa14a097cb90d1d119c',NULL,NULL,NULL,NULL,NULL,NULL,'SC',NULL,NULL,NULL,NULL,'usuario'),(13,'Cedrick Moraes','cedrick.moraes@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1001',NULL,NULL,NULL,'Cantor e compositor de Criciúma com mais de 20 anos de estrada e organizador do Festival do Ar.','Criciúma','SC',NULL,NULL,NULL,NULL,'usuario'),(14,'Banda Fucking Project','fucking.project@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1002',NULL,NULL,NULL,'Banda de Criciúma formada em 2018 com rock, grunge e hardcore autorais.','Criciúma','SC',NULL,NULL,NULL,NULL,'usuario'),(15,'Nando Mason e Os Chacais','nando.mason@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1003',NULL,NULL,NULL,'Rock, country e blues com material autoral nos palcos do Sul de Santa Catarina.','Criciúma','SC',NULL,NULL,NULL,NULL,'usuario'),(16,'Barba Rala','barba.rala@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1004',NULL,NULL,NULL,'Rock progressivo e pesado com letras reflexivas em português.','Santa Rosa do Sul','SC',NULL,NULL,NULL,NULL,'usuario'),(17,'Poliana Peres','poliana.peres@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1005',NULL,NULL,NULL,'Cantora e compositora de MPB e rock autoral, com dois álbuns lançados.','Criciúma','SC',NULL,NULL,NULL,NULL,'usuario'),(18,'Fê Smania','fe.smania@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1006',NULL,NULL,NULL,'Pop rock, alternativo e emo-pop, nascida em Santa Catarina.','Criciúma','SC',NULL,NULL,NULL,NULL,'usuario'),(19,'Banda Hero','banda.hero@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1007',NULL,NULL,NULL,'Pop punk e rock alternativo com covers e autorais desde 2022.','Criciúma','SC',NULL,NULL,NULL,NULL,'usuario'),(20,'Novela','banda.novela@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1008',NULL,NULL,NULL,'Banda de Içara com história desde 2004 e volta aos palcos após hiato de 10 anos.','Içara','SC',NULL,NULL,NULL,NULL,'usuario'),(21,'Ponto Zero','ponto.zero@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1009',NULL,NULL,NULL,'Vencedora do Prêmio Mundial Rock 2024, referência da cena independente da região.','Criciúma','SC',NULL,NULL,NULL,NULL,'usuario'),(22,'Lúdi Lucas Veveto','ludi.veveto@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1010',NULL,NULL,NULL,'Cantor, compositor e produtor musical do Selo Ambulante, com guitarra e poesia.','Criciúma','SC',NULL,NULL,NULL,NULL,'usuario'),(23,'Héck Laurindo & Banda','heck.laurindo@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1011',NULL,NULL,NULL,'Cantor natural de Cocal do Sul com pop e rock clássico dos anos 80 e 90.','Cocal do Sul','SC',NULL,NULL,NULL,NULL,'usuario'),(24,'Ozel','ozel.banda@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1012',NULL,NULL,NULL,'Grupo de pop e rock nacional com mais de uma década de estrada no Sul de SC.','Cocal do Sul','SC',NULL,NULL,NULL,NULL,'usuario'),(25,'Orquestra Sinfônica de Criciúma','orquestra.criciuma@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1013',NULL,NULL,NULL,'Orquestra de Criciúma com 40 músicos, repertório clássico e popular.','Criciúma','SC',NULL,NULL,NULL,NULL,'empresa'),(26,'Saimon Campos Fotografia','saimon.campos@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1014',NULL,NULL,NULL,'Fotógrafo de eventos em Criciúma com estilos tradicional, artístico e de autor.','Criciúma','SC',NULL,NULL,NULL,NULL,'empresa'),(27,'Dany & Michel Creative Photo','dany.michel@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1015',NULL,NULL,NULL,'Dupla de fotógrafos de Criciúma atuando em casamentos desde 2007.','Criciúma','SC',NULL,NULL,NULL,NULL,'empresa'),(28,'Lukas Campos Fotografia','lukas.campos@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1016',NULL,NULL,NULL,'Fotógrafo de casamentos, formaturas e família, natural de Santa Catarina.','Criciúma','SC',NULL,NULL,NULL,NULL,'usuario'),(29,'Shoot Films','shoot.films@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1017',NULL,NULL,NULL,'Produtora de vídeos de Criciúma desde 2011 para o mercado publicitário.','Criciúma','SC',NULL,NULL,NULL,NULL,'empresa'),(30,'Creative Eyes Filmes','creative.eyes@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1018',NULL,NULL,NULL,'Produtora de vídeo e podcast em Criciúma com mais de 7 anos de estrada.','Criciúma','SC',NULL,NULL,NULL,NULL,'empresa'),(31,'Flyah Audiovisual','flyah.audiovisual@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1019',NULL,NULL,NULL,'Produtora criativa de Criciúma especializada em filmes aéreos com drones e FPV.','Criciúma','SC',NULL,NULL,NULL,NULL,'empresa'),(32,'Escola de Dança Viviane Candiotto','viviane.candiotto@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1020',NULL,NULL,NULL,'Escola de dança do Centro de Criciúma com ballet e dança contemporânea.','Criciúma','SC',NULL,NULL,NULL,NULL,'empresa'),(33,'Grupo Folclórico Italiano Valsugana','valsugana@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1021',NULL,NULL,NULL,'Grupo folclórico italiano de Criciúma que preserva a tradição desde 1987.','Criciúma','SC',NULL,NULL,NULL,NULL,'empresa'),(34,'Cia de Dança Simony e Jéssica','simony.jessica@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1022',NULL,NULL,NULL,'Companhia de ballet de Criciúma com repertório clássico e contemporâneo.','Criciúma','SC',NULL,NULL,NULL,NULL,'empresa'),(35,'Cambuca Design','cambuca.design@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1023',NULL,NULL,NULL,'Estúdio de design de Criciúma criado pela designer Tami Brígido em 2018.','Criciúma','SC',NULL,NULL,NULL,NULL,'empresa'),(36,'Estúdio Garça','estudio.garca@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1024',NULL,NULL,NULL,'Estúdio de design de marcas de Criciúma, desde 2020, com visão humanizada.','Criciúma','SC',NULL,NULL,NULL,NULL,'empresa'),(37,'Jorge Luiz Teixeira','jorginho.design@eventovivo.com','$2y$10$oayTc9xIBj46I4JsFVdRheOuGdKCXNE/B5SCp3NDtwrErk5R7lTIC','(48) 99101-1025',NULL,NULL,NULL,'Designer gráfico e desenhista de Criciúma com mais de 20 anos de experiência.','Criciúma','SC',NULL,NULL,NULL,NULL,'usuario');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-09 20:01:18

-- ============================================================
-- Origem: eventovivo_eventos.sql
-- ============================================================
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: eventovivo
-- ------------------------------------------------------
-- Server version	5.5.20-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `eventos`
--

DROP TABLE IF EXISTS `eventos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eventos` (
  `id_evento` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_inicio_evento` date NOT NULL,
  `data_fim_evento` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `endereco` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cep` varchar(9) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL DEFAULT '0.00',
  `vagas` int(11) NOT NULL,
  `imagem_capa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `faixa_etaria` enum('Livre','10','12','14','16','18+') COLLATE utf8mb4_unicode_ci DEFAULT 'Livre',
  `data_publicacao` datetime DEFAULT NULL,
  `arquivado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_evento`),
  KEY `fk_evento_usuario` (`usuario_id`),
  KEY `fk_evento_categoria` (`categoria_id`),
  KEY `idx_evento_arquivado` (`arquivado`),
  KEY `idx_evento_data_inicio` (`data_inicio_evento`),
  KEY `idx_evento_data_fim` (`data_fim_evento`),
  CONSTRAINT `fk_evento_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_eventos` (`id_categoria`) ON UPDATE CASCADE,
  CONSTRAINT `fk_evento_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos`
--

LOCK TABLES `eventos` WRITE;
/*!40000 ALTER TABLE `eventos` DISABLE KEYS */;
INSERT INTO `eventos` VALUES (4,6,4,'Feira Cultural Regional','Feira com artistas, artesãos, apresentações musicais e exposições.','2026-10-15','2026-10-17','10:00:00','22:00:00','Praça Central','50','Criciúma','SC','88806-000',0.00,1000,'feira_cultural.jpg','Livre','2026-08-20 11:30:00',0),(5,6,5,'Festival de Primavera','Festival com música ao vivo, apresentações artísticas e gastronomia.','2026-11-07','2026-11-08','14:00:00','23:00:00','Parque Centenário','500','Criciúma','SC','88807-000',35.00,800,'festival_primavera.jpg','Livre','2026-08-20 12:00:00',0),(6,6,1,'MeteHorror','O espaço é transformado em um cenário temático de terror e caos, funcionando como o festival de Dia das Bruxas da casa voltado à cena rock/alternativa. A organização costuma incentivar o público a comparecer com fantasias ou trajes a temática','2026-10-24','2026-10-25','19:00:00','23:59:00','Rua Seis de Janeiro, 111 – Centro, Criciúma - SC','','Criciuma','SC','88801-055',50.00,100,'festival_musica.jpg','16','2026-10-29 17:09:56',0),(8,6,1,'Show de Fresno em Florianópolis','Apresentação da banda Fresno anunciada para Florianópolis. A agenda consultada indica 6 de novembro de 2026, às 20h. O local, abertura dos portões, ingressos e classificação indicativa devem ser confirmados com o organizador.','2026-11-06','2026-11-06','20:00:00','23:00:00','Local a confirmar',NULL,'Florianópolis','SC',NULL,0.00,500,'evento_fresno_floripa.webp','16','2026-11-03 00:14:38',0),(12,6,1,'Armandinho e Vitor Kley | Encontro de Gigantes em BC','Show de Armandinho e Vitor Kley anunciado para 13 de novembro de 2026, às 20h, no Music Park Balneário Camboriú. Confirmar horário de abertura, preços, classificação e detalhes de acesso na página oficial de ingressos.','2026-11-13','2026-11-13','20:00:00','23:59:00','Music Park Balneário Camboriú',NULL,'Balneário Camboriú','SC',NULL,0.00,1000,'evento_armandinho_bc.jpg','16','2026-11-03 00:14:38',0),(14,6,1,'Armandinho e Vitor Kley - Floripa','Show de Armandinho e Vitor Kley anunciado para 14 de novembro de 2026, às 20h, no Stage Music Park, em Florianópolis. Confirmar ingressos, abertura dos portões, classificação indicativa e demais condições com a organização.','2026-11-14','2026-11-14','20:00:00','23:59:00','Stage Music Park',NULL,'Florianópolis','SC',NULL,0.00,1000,'evento_armandinho_floripa.jpg','16','2026-11-03 00:14:38',0),(15,6,1,'Guilherme Arantes - 50 anos de carreira','Show comemorativo de 50 anos de carreira de Guilherme Arantes anunciado para 20 e 21 de novembro de 2026 na Arena Opus, em São José. A agenda consultada informa ingressos a partir de R$ 150. Confirmar a sessão/data escolhida, horários, preço atualizado e classificação indicativa na bilheteria oficial.','2026-11-20','2026-11-21','21:00:00','23:30:00','Arena Opus',NULL,'São José','SC',NULL,150.00,1000,'evento_guilherme_arantes.png','16','2026-11-03 00:14:38',0),(17,6,1,'Terra Celta em Balneário Camboriú','Show da banda Terra Celta, com repertório autoral e canções conhecidas do grupo. A agenda do BC Convention lista o período de 6 de novembro a 7 de dezembro de 2026 no The Bunker 663; confirmar as datas e horários de cada apresentação com o local.','2026-11-06','2026-12-07','20:00:00','23:59:00','The Bunker 663',NULL,'Balneário Camboriú','SC',NULL,0.00,500,'evento_terra_celta.webp','Livre','2026-11-03 00:32:11',0),(18,6,1,'Final do Santa Catarina Canta 2026','Grande final do Festival de Música Brasileira, com os finalistas catarinenses e shows de Fernando e Sorocaba e Sérgio Reis acompanhados pela Camerata Florianópolis. Entrada gratuita.','2026-11-08','2026-11-08','17:00:00','23:59:00','Avenida Beira-Mar Continental',NULL,'Florianópolis','SC',NULL,0.00,5000,'evento_sc_canta_2026.jpg','Livre','2026-11-03 00:32:11',0),(19,6,1,'Show Nacional com Yasmin Santos','Show de Yasmin Santos anunciado para 13 e 14 de novembro de 2026 no SHED Club BC. A agenda consultada não detalha o horário; 22h–4h é apenas um valor de demonstração, confirme com o organizador.','2026-11-13','2026-11-14','22:00:00','04:00:00','SHED Club BC',NULL,'Balneário Camboriú','SC',NULL,0.00,1000,'evento_yasmin_santos.webp','18+','2026-11-03 00:32:11',0),(20,6,1,'Rodrigo Teaser - Tributo a Michael Jackson','Espetáculo musical de Rodrigo Teaser em tributo a Michael Jackson, anunciado para 14 de novembro de 2026 no Music Park Balneário Camboriú. Horário de início e preço não foram divulgados na agenda consultada; confirmar com o organizador.','2026-11-14','2026-11-14','21:00:00','23:59:00','Music Park Balneário Camboriú',NULL,'Balneário Camboriú','SC',NULL,0.00,1000,'evento_rodrigo_teaser.webp','Livre','2026-11-03 00:32:11',0),(21,6,1,'Batuke do Pretinho com Maria Rita e Entre Elas','Roda de samba comandada por Pretinho da Serrinha, com participações de Maria Rita e do grupo Entre Elas. Show em 15 de novembro de 2026, às 18h, no Stage Music Park. O valor zero abaixo é placeholder e não indica gratuidade; confirmar preços e lotes na Ticket360.','2026-11-15','2026-11-16','18:00:00','06:00:00','Stage Music Park - Rodovia Maurício Sirotsky Sobrinho','1050','Florianópolis','SC','88053-700',0.00,2410,'evento_batuke_pretinho.webp','18+','2026-11-03 00:32:11',0),(24,6,4,'3º Festival Cultural Chinês de Santa Catarina','Evento familiar gratuito com apresentações de Kung Fu e Tai Chi, palestras e atividades de valorização da cultura chinesa.','2026-11-07','2026-11-07','10:00:00','16:00:00','Multi Open Shopping - Boulevard','3339','Florianópolis','SC','88048-301',0.00,400,'evento_cultura_festival_chines.jpg','Livre','2026-11-03 12:00:00',0),(25,6,4,'4ª edição do Haru Matsuri','Celebração da cultura e da primavera japonesa com gastronomia, música tradicional, oficinas criativas e artesanato. Entrada gratuita.','2026-11-07','2026-11-08','10:00:00','17:00:00','Jardim Botânico de Florianópolis','742','Florianópolis','SC','88034-000',0.00,700,'evento_cultura_haru_matsuri.jpg','Livre','2026-11-03 12:00:00',0),(26,6,4,'8ª Feira de Cerâmica de Florianópolis','Feira gratuita com 15 expositores de cerâmica e peças autorais, no boulevard do Multi Open Shopping.','2026-11-13','2026-11-15','12:00:00','20:00:00','Multi Open Shopping - Boulevard','3339','Florianópolis','SC','88048-301',0.00,350,'evento_cultura_feira_ceramica.jpg','Livre','2026-11-03 12:00:00',0),(27,6,4,'Exposição A Reinvenção da Natureza — Zé Paiva','Exposição gratuita de fotografia e arte sobre a Mata Atlântica, com curadoria de Ana Sabiá e participação da artista Clara Fernandes. Visitação em dias úteis.','2026-10-22','2027-02-26','08:00:00','18:30:00','Galeria de Arte do Sesc Concórdia','620','Concórdia','SC','89700-000',0.00,150,'evento_cultura_ze_paiva.jpg','Livre','2026-11-03 12:00:00',0),(28,6,4,'Natal em Pomerode 2026','Programação natalina com Mercado de Natal, decoração, artesanato, gastronomia, apresentações culturais e atividades para famílias.','2026-11-19','2027-01-03','16:00:00','22:00:00','Centro Cultural de Pomerode','111','Pomerode','SC','89107-000',0.00,1000,'evento_cultura_natal_pomerode.png','Livre','2026-11-03 12:00:00',0),(29,6,3,'A Dança das Flores','Espetáculo de dança com sessão no Teatro Governador Pedro Ivo, em Florianópolis. Ingresso anunciado a partir de R$ 60,00.','2026-11-21','2026-11-21','18:30:00','20:30:00','Teatro Governador Pedro Ivo - Rodovia SC-401, km 5','4600','Florianópolis','SC','88032-005',60.00,500,'evento_danca_danca_das_flores.jpeg','Livre','2026-11-03 12:00:00',0),(30,6,3,'Dança — Uma História em Movimento','Espetáculo de dança no Teatro Ademir Rosa (CIC), em Florianópolis. Ingressos anunciados a partir de R$ 35,00.','2026-12-03','2026-12-03','20:00:00','22:00:00','Teatro Ademir Rosa - Avenida Governador Irineu Bornhausen','5600','Florianópolis','SC','88025-202',35.00,900,'evento_danca_historia_movimento.png','Livre','2026-11-03 12:00:00',0),(32,6,3,'Cartas ao Mar — XVII Mostra de Dança do Casarão da Dança','Mostra de dança do Casarão da Dança no Teatro Ademir Rosa, em Florianópolis. Ingressos anunciados a partir de R$ 50,00.','2026-12-09','2026-12-09','19:30:00','21:30:00','Teatro Ademir Rosa - Avenida Governador Irineu Bornhausen','5600','Florianópolis','SC','88025-202',50.00,900,'evento_danca_cartas_ao_mar.png','Livre','2026-11-03 12:00:00',0),(33,6,3,'Anos 2000 — espetáculo de fim de ano da Central da Dança','Espetáculo de fim de ano da Central da Dança no Teatro Ademir Rosa, em Florianópolis. Ingressos anunciados a partir de R$ 50,00.','2026-12-11','2026-12-11','20:00:00','22:00:00','Teatro Ademir Rosa - Avenida Governador Irineu Bornhausen','5600','Florianópolis','SC','88025-202',50.00,900,'evento_danca_anos_2000.jpg','Livre','2026-11-03 12:00:00',0),(34,6,5,'2º Festival Catarinense de Hip Hop — Etapa Criciúma','Festival gratuito de cultura urbana com graffiti, breaking, DJs, MCs, batalhas e atividades formativas em espaços culturais parceiros de Criciúma.','2026-11-21','2026-11-22','09:00:00','21:00:00','Casa do Hip Hop Flor e Ser - Vila Zuleima','s/n','Criciúma','SC','88805-000',0.00,500,'evento_festival_hiphop.jpeg','Livre','2026-11-03 12:00:00',0),(35,6,5,'18º Festival Brasileiro da Cerveja','Festival de cervejarias artesanais, gastronomia e cultura cervejeira no Parque Vila Germânica. O ingresso informado a partir de R$ 119,00 refere-se ao primeiro lote do evento Degusta, realizado no mesmo local e período.','2027-03-03','2027-03-06','19:00:00','23:00:00','Parque Vila Germânica - Rua Alberto Stein','199','Blumenau','SC','89036-200',119.00,1200,'evento_festival_cerveja.jpg','18+','2026-11-03 12:00:00',0),(36,6,5,'42ª Festa Pomerana','Doze dias de cultura e tradições germânicas, com desfiles, grupos folclóricos, gastronomia típica, competições, feira e parque de diversões. Ingressos a partir de R$ 25,00.','2027-01-13','2027-01-24','18:00:00','23:59:00','Pavilhão Municipal de Eventos - Avenida 21 de Janeiro','2700','Pomerode','SC','89107-000',25.00,1500,'evento_festival_festa_pomerana.jpg','Livre','2026-11-03 12:00:00',0),(37,6,5,'Sommer Festival 2027','Festival de verão no Parque Vila Germânica, com programação de música, gastronomia e atrações culturais. A Prefeitura confirmou os dias 21 a 23/01; o show de Simone Mendes em 23/01 tem ingressos a partir de R$ 55,00.','2027-01-21','2027-01-23','18:00:00','23:59:00','Pavilhão Spaten - Parque Vila Germânica, Rua Alberto Stein','199','Blumenau','SC','89036-200',55.00,3000,'evento_festival_sommer.jpg','Livre','2026-11-03 12:00:00',0),(38,6,5,'Festival de Forró do Rosa — 5 anos','Quatro dias de forró na Praia do Rosa, com bailes, oficinas, atividades na lagoa e apresentações de trios. Passaportes anunciados a partir de R$ 290,00.','2027-03-11','2027-03-14','21:00:00','23:59:00','Mar del Rosa - Praia do Rosa','s/n','Imbituba','SC','88780-000',60.00,800,'evento_festival_forro_rosa.png','Livre','2026-11-03 12:00:00',0),(39,6,2,'Elisa e os Cisnes Selvagens — 8º Animaneco','Espetáculo de teatro de formas animadas com Teatro por um Triz. Sessões gratuitas às 9h e 15h no Teatro Sesc de Joinville.','2026-11-04','2026-11-04','09:00:00','16:00:00','Teatro Sesc - Rua Itaiópolis','470','Joinville','SC','89204-100',0.00,180,'evento_teatro_animaneco.jpg','Livre','2026-11-03 12:00:00',0),(40,6,2,'Pintor da Palavra — 8º Animaneco','Espetáculo gratuito do Grupo Teatro CasAmarela, de Brusque, inspirado nas cartas e pinturas de Vincent van Gogh. Sessões no Teatro Sesc de Joinville.','2026-11-05','2026-11-05','19:00:00','21:00:00','Teatro Sesc - Rua Itaiópolis','470','Joinville','SC','89204-100',0.00,180,'evento_teatro_pintor_palavra.png','Livre','2026-11-03 12:00:00',0),(41,6,2,'Mulher Pássaro — 8º Animaneco','Performance visual de Genifer Gerhardt que combina teatro de bonecos em miniatura, circo e intervenção urbana. Sessão gratuita às 20h no Teatro Sesc de Joinville.','2026-11-06','2026-11-06','20:00:00','21:00:00','Teatro Sesc - Rua Itaiópolis','470','Joinville','SC','89204-100',0.00,180,'evento_teatro_mulher_passaro.jpg','Livre','2026-11-03 12:00:00',0),(42,6,2,'TÍMON — Persona Cia de Teatro','Montagem de Shakespeare e Thomas Middleton ambientada nos anos 1920. Duas apresentações no Teatro Camarim, dias 7 e 8 de novembro, às 19h30. Ingresso solidário anunciado a R$ 40,00 com doação de alimento.','2026-11-07','2026-11-08','19:30:00','21:30:00','Centro Cultural Camarim - Rua Professora Antonieta de Barros','976','Florianópolis','SC','88070-700',40.00,100,'evento_teatro_timon.jpg','14','2026-11-03 12:00:00',0);
/*!40000 ALTER TABLE `eventos` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-09 20:01:18

-- ============================================================
-- Origem: eventovivo_freelancers.sql
-- ============================================================
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: eventovivo
-- ------------------------------------------------------
-- Server version	5.5.20-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `freelancers`
--

DROP TABLE IF EXISTS `freelancers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `freelancers` (
  `id_freelancer` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `profissao` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `experiencia` text COLLATE utf8mb4_unicode_ci,
  `valor_hora` decimal(10,2) DEFAULT NULL,
  `rede_social` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_freelancer`),
  UNIQUE KEY `uq_freelancer_usuario` (`usuario_id`),
  KEY `fk_freelancer_usuario` (`usuario_id`),
  KEY `fk_freelancer_categoria` (`categoria_id`),
  CONSTRAINT `fk_freelancer_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_servicos` (`id_categoria`) ON UPDATE CASCADE,
  CONSTRAINT `fk_freelancer_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `freelancers`
--

LOCK TABLES `freelancers` WRITE;
/*!40000 ALTER TABLE `freelancers` DISABLE KEYS */;
INSERT INTO `freelancers` VALUES (1,1,1,'Músico','Músico especializado em apresentações ao vivo e eventos.','Mais de 5 anos de experiência em apresentações musicais.',150.00,'@lucasmusic'),(2,2,1,'Cantora','Cantora especializada em música brasileira e pop.','Mais de 5 anos realizando apresentações em bares, eventos e festivais.',180.00,'@anabeatriz.music'),(3,3,2,'Fotógrafo','Fotógrafo especializado em shows, eventos e ensaios.','Mais de 7 anos trabalhando com fotografia profissional.',120.00,'@marcosfoto'),(4,5,4,'Dançarino','Dançarino especializado em apresentações urbanas e coreografias.','Participação em grupos de dança e apresentações culturais.',100.00,'@gabrieldanca'),(5,4,5,'Designer Gráfico','Designer especializado em cartazes e identidade visual para eventos.','Experiência na criação de materiais gráficos para artistas e produtores.',80.00,'@eventossuldesign'),(6,6,1,'Baterista','Toco muito mesmo','6 anos',0.00,''),(7,13,1,'Cantor e Compositor','Cantor, compositor e produtor independente ligado à cena musical de Criciúma.','Mais de 20 anos de carreira. Com a banda Balthazar participou do Festival Criciúma Rock 2014, Circuito SESC de Música 2017 e festivais Brada Mundo e Sonora. Produziu o álbum Encantamento.',300.00, '@cedrickmoraes'),(8,14,1,'Banda de Rock','Banda de Criciúma que une rock, grunge e hardcore em uma identidade própria.','2 EPs, 1 single e 3 clipes lançados. Presença na cena autoral catarinense e shows por todo o Sul de SC.',350.00,NULL),(9,15,1,'Banda de Rock e Blues','Banda de Criciúma com repertório de rock, country, blues e rock nacional.','Mais de quatro anos nos palcos, com material autoral e apresentações em eventos da região.',350.00,NULL),(10,16,1,'Banda de Rock Progressivo','Banda de Santa Rosa do Sul com quase 8 anos de estrada em rock progressivo e pesado.','Finalista do Festival João Rock em São Paulo e eleita Banda Revelação em Balneário Camboriú. Dividiu o palco com Pitty, Nação Zumbi, CPM 22, Raimundos e Matanza.',400.00, '@BarbaRalaOficial'),(11,17,1,'Cantora e Compositora','Cantora e compositora que transita entre a MPB e o rock autoral.','Álbum de estreia Terça-feira (2018) e Epifania (2020).',250.00, '@poliana.peres'),(12,18,1,'Cantora e Compositora','Projeto autoral de pop rock, alternativo e emo-pop.','EP Unilateral, singles Silêncio e Odeio e primeiro álbum completo em gravação.',250.00, '@fesmania'),(13,19,1,'Banda de Pop Punk','Banda de pop punk e rock alternativo formada em 2022.','5 singles, 1 EP e 1 álbum lançado, com novo trabalho a caminho.',350.00,NULL),(14,20,1,'Banda de Indie e Pop','Banda de Içara que começou em 2004 no Festival do Brasil.','Gravou 3 singles com Adair Daufembach entre 2005 e 2011. Depois de um hiato de 10 anos, voltou aos palcos com novos integrantes.',350.00,NULL),(15,21,1,'Banda de Rock','Banda vencedora do Prêmio Mundial Rock 2024.','Premiada no cenário mundial do rock e presente nos festivais de música independente do Sul de SC.',400.00, '@realpontozero'),(16,22,1,'Cantor e Produtor Musical','Cantor, compositor e produtor musical; guitarra e poesia no repertório.','Como artista do Selo Ambulante, lançou em 2023 os singles Da Cor do Dia e TRETA.',250.00,NULL),(17,23,1,'Cantor de Pop e Rock','Projeto de pop e rock clássico inspirado em Queen, Bon Jovi, Bryan Adams e Tina Turner.','Dez anos de estrada com a banda, cerca de três shows por semana em casamentos, formaturas e eventos por toda Santa Catarina.',300.00,NULL),(18,24,1,'Banda de Pop e Rock','Banda nascida na Cocal Fest que resgata clássicos do pop e do rock nacional.','Mais de uma década de shows em casamentos, pubs, festas e eventos corporativos.',350.00,NULL),(19,25,1,'Orquestra e Música Erudita','Orquestra criada em parceria com a Fundação Cultural de Criciúma para levar música erudita à cidade.','Concertos no Espaço Cultural Angeloni, na Casa da Cultura e no Auditório Ruy Hülse da Unesc, com regência de maestros convidados.',600.00,NULL),(20,26,2,'Fotógrafo de Eventos','Fotógrafo capacitado para todos os estilos de eventos, do tradicional ao artístico e de autor.','Profissional de Criciúma com avaliação 5.0 em plataforma de casamentos e trabalho em casamentos, formaturas e eventos sociais.',180.00,NULL),(21,27,2,'Fotógrafos de Casamento','Equipe de fotografia de casamentos e eventos, um dos profissionais mais reconhecidos da região.','Atuando desde 2007, com avaliação 4.8 e dezenas de casamentos registrados em Criciúma e região.',250.00,NULL),(22,28,2,'Fotógrafo de Casamentos e Família','Fotografia que registra sentimentos e emoções em eventos e ensaios.','Carreira iniciada em 2012 em eventos corporativos; especializado desde 2014 em famílias, ensaios, casamentos e formaturas. Fotos premiadas e atendimento em Criciúma e região.',200.00, '@lukascamposfotografia'),(23,29,3,'Produtora de Vídeos','Soluções audiovisuais para clientes e agências de comunicação e marketing.','Desde 2011 com vídeo institucional, TV, propaganda em vídeo, motion graphics, After Effects e Premiere.',250.00,NULL),(24,30,3,'Produtora de Vídeo e Podcast','Hub criativo com produtora profissional, duas salas de podcast e marketing digital.','Documentários, vídeos institucionais e de vendas, estudos de caso e podcasts para lançamentos e marcas.',250.00, '@creativeeyesbr'),(25,31,3,'Audiovisual com Drones e FPV','Filmes aéreos com drones e FPV para institucionais, publicitários e eventos.','Nota 5.0 no Google com mais de 10 avaliações de clientes em Santa Catarina.',300.00, '@flyahaudiovisual'),(26,32,4,'Escola de Dança','Escola de dança com formação em ballet e repertório clássico e contemporâneo.','Listada entre os grupos de dança de Criciúma pela Fundação Catarinense de Cultura e participante de mostras como o Unesc em Dança.',120.00,NULL),(27,33,4,'Grupo de Dança Folclórica','Danças e cantos do folclore italiano com coreografias pesquisadas na Itália.','Fundado em junho de 1987. Grupos infantil e adulto, apresentações gratuitas e intercâmbio com coreógrafos italianos, como Dário Danile, em 2013.',200.00,NULL),(28,34,4,'Companhia de Ballet','Companhia de dança com ballet de repertório e trabalhos solo, duo e grupo.','Participante do 13º Unesc em Dança com obras do repertório clássico de Marius Petipa.',150.00,NULL),(29,35,5,'Designer Gráfico e Branding','Estúdio que mistura ideias, conceitos e valores em soluções visuais e comunicação digital.','Desde 2018 com identidade visual, jornada de marca e conteúdo criativo para marcas do Sul de Santa Catarina.',120.00, '@cambucadesign'),(30,36,5,'Designer de Marcas','Especialista em criação de identidades visuais para marcas que desejam expressar sua essência.','Fundado em 2020 com metodologia de escuta, pesquisas e análises para identidades visuais personalizadas.',130.00, '@est.garca'),(31,37,5,'Designer Gráfico','Criação de logomarca, identidade visual corporativa, catálogos, livros e anúncios.','Mais de 20 anos de mercado, fundador da Gêmeos Design em Criciúma e atendimento a dezenas de clientes satisfeitos.',100.00, '@jorginhocoach');
/*!40000 ALTER TABLE `freelancers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-09 20:01:18

-- ============================================================
-- Origem: eventovivo_portfolio.sql
-- ============================================================
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: eventovivo
-- ------------------------------------------------------
-- Server version	5.5.20-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `portfolio`
--

DROP TABLE IF EXISTS `portfolio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `portfolio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `freelancer_id` int(11) NOT NULL,
  `imagem` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `fk_portfolio_freelancer` (`freelancer_id`),
  CONSTRAINT `fk_portfolio_freelancer` FOREIGN KEY (`freelancer_id`) REFERENCES `freelancers` (`id_freelancer`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portfolio`
--

LOCK TABLES `portfolio` WRITE;
/*!40000 ALTER TABLE `portfolio` DISABLE KEYS */;
INSERT INTO `portfolio` VALUES (1,1,'lucas_show.jpg','Apresentação musical realizada em evento cultural.'),(2,2,'ana_show.jpg','Apresentação da cantora Ana Beatriz em festival regional.'),(3,3,'marcos_evento.jpg','Fotografia profissional realizada durante um show ao vivo.'),(4,4,'gabriel_danca.jpg','Apresentação de dança urbana em evento cultural.'),(5,5,'design_festival.jpg','Identidade visual criada para divulgação de festival musical.');
/*!40000 ALTER TABLE `portfolio` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-09 20:01:18

-- ============================================================
-- Origem: eventovivo_carrossel_fotos.sql
-- ============================================================
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: eventovivo
-- ------------------------------------------------------
-- Server version	5.5.20-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `carrossel_fotos`
--

DROP TABLE IF EXISTS `carrossel_fotos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrossel_fotos` (
  `id_foto` int(11) NOT NULL AUTO_INCREMENT,
  `freelancer_id` int(11) NOT NULL,
  `imagem` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `legenda` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_cadastro` datetime DEFAULT NULL,
  PRIMARY KEY (`id_foto`),
  KEY `fk_carrossel_freelancer` (`freelancer_id`),
  KEY `idx_carrossel_fre_data` (`freelancer_id`,`data_cadastro`,`id_foto`),
  CONSTRAINT `fk_carrossel_freelancer` FOREIGN KEY (`freelancer_id`) REFERENCES `freelancers` (`id_freelancer`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrossel_fotos`
--

LOCK TABLES `carrossel_fotos` WRITE;
/*!40000 ALTER TABLE `carrossel_fotos` DISABLE KEYS */;
INSERT INTO `carrossel_fotos` VALUES (1,1,'portfolio_lucas.png','Apresentação musical realizada em evento cultural.','2026-10-08 13:59:17'),(2,2,'portfolio_ana.png','Apresentação da cantora Ana Beatriz em festival regional.','2026-10-08 13:59:17'),(3,3,'portfolio_marcos.png','Fotografia profissional realizada durante um show ao vivo.','2026-10-08 13:59:17'),(4,4,'portfolio_gabriel.png','Apresentação de dança urbana em evento cultural.','2026-10-08 13:59:17'),(5,5,'portfolio_eventossul.png','Identidade visual criada para divulgação de festival musical.','2026-10-08 13:59:17'),(30,2,'freelancer_6ac7e8c8df37b.jpg','Rock in rio','2026-10-08 16:02:32'),(31,2,'freelancer_6ac7eac9ab6cb.png','asegaewg','2026-10-08 16:11:05');
/*!40000 ALTER TABLE `carrossel_fotos` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-09 20:01:18

-- ============================================================
-- Origem: eventovivo_avaliacoes.sql
-- ============================================================
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: eventovivo
-- ------------------------------------------------------
-- Server version	5.5.20-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `avaliacoes`
--

DROP TABLE IF EXISTS `avaliacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `avaliacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `avaliador` int(11) NOT NULL,
  `freelancer_id` int(11) NOT NULL,
  `nota` int(11) NOT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `data_avaliacao` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_avaliacao_usuario_freelancer` (`avaliador`,`freelancer_id`),
  KEY `fk_avaliacao_usuario` (`avaliador`),
  KEY `fk_avaliacao_freelancer` (`freelancer_id`),
  CONSTRAINT `fk_avaliacao_freelancer` FOREIGN KEY (`freelancer_id`) REFERENCES `freelancers` (`id_freelancer`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_avaliacao_usuario` FOREIGN KEY (`avaliador`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avaliacoes`
--

LOCK TABLES `avaliacoes` WRITE;
/*!40000 ALTER TABLE `avaliacoes` DISABLE KEYS */;
INSERT INTO `avaliacoes` VALUES (1,2,1,5,'Excelente músico e ótima apresentação. Recomendo.','2026-08-15 14:30:00'),(2,1,2,5,'Excelente cantora e ótima interação com o público.','2026-08-16 16:00:00'),(3,4,3,5,'Fotografias de ótima qualidade e entrega rápida.','2026-08-17 10:20:00'),(4,1,4,5,'Excelente dançarino e muito profissional.','2026-08-18 18:45:00'),(5,3,5,4,'Bom trabalho de design e ótima qualidade visual.','2026-08-19 12:10:00'),(6,4,1,5,'Show muito bem ensaiado e som impecável. Fez a festa do nosso casamento em Içara ficar ainda mais especial.','2026-08-01 09:00:00'),(7,5,1,4,'Pontual, atencioso e com repertório certeiro. Agradou tanto os mais velhos quanto os jovens.','2026-08-04 09:00:00'),(8,3,1,5,'Excelente custo-benefício e negociação tranquila. Já estamos combinando a próxima data.','2026-08-07 09:00:00'),(9,3,2,5,'Voz linda e presença de palco marcante. Cantou MPB e pop com uma naturalidade que encantou todo mundo.','2026-08-05 09:00:00'),(10,4,2,4,'Interagiu com o público do começo ao fim e o repertório foi bem montado. Comunicação rápida desde o primeiro contato.','2026-08-08 09:00:00'),(11,5,3,5,'Fotos com naturalidade incrível: registrou momentos que nem sabíamos que estavam acontecendo.','2026-08-09 09:00:00'),(12,1,3,4,'Entrega antes do prazo e tudo organizado em pastas. Trabalhou tranquilo sem interromper o evento.','2026-08-12 09:00:00'),(13,3,4,5,'Coreografia impecável e energia contagiante. Foi o ponto alto do nosso evento corporativo.','2026-08-13 09:00:00'),(14,4,4,4,'Muito profissional: chegou cedo para ensaiar com a equipe e apresentou tudo certinho.','2026-08-16 09:00:00'),(15,2,4,5,'Dançarino versátil, do urbano ao contemporâneo. O público aplaudiu de pé.','2026-08-19 09:00:00'),(16,1,5,5,'Cartazes chamativos e identidade visual consistente em tudo. A divulgação do festival ficou muito mais forte.','2026-08-17 09:00:00'),(17,2,5,4,'Atendeu rápido às mudanças de última hora e entregou os arquivos prontos para impressão.','2026-08-20 09:00:00'),(18,7,6,5,'Bateria firme e no tempo certo: segurou a banda inteira no nosso evento.','2026-04-01 09:00:00'),(19,8,6,4,'Ensaio sério e sem drama. Chegou preparado e resolveu qualquer imprevisto no palco.','2026-04-05 14:17:00'),(20,13,6,5,'Som pesado bem resolvido. Dá para ver que são 6 anos de estrada ali.','2026-04-09 09:34:00'),(21,35,7,5,'Cantou com a voz de quem tem mais de 20 anos de estrada. A plateia não parou de cantar junto.','2026-04-08 12:13:00'),(22,1,7,5,'Ouvimos o álbum Encantamento antes do show e ele trouxe exatamente aquela atmosfera para o palco.','2026-04-12 17:30:00'),(23,2,7,4,'Presença de palco ótima e conversa tranquila desde o orçamento. Vale o valor da hora cobrado.','2026-04-16 12:47:00'),(24,3,7,5,'Repertório bem construído e transições suaves entre as músicas. Noite redonda de show.','2026-04-20 17:04:00'),(25,3,8,5,'Grunge e hardcore bem tocados, com os clipes novos tocando ao vivo. A juventude enlouqueceu.','2026-04-15 15:26:00'),(26,4,8,4,'Show bem ensaiado: os dois EPs tocaram redondo e a interação com o público foi ótima.','2026-04-19 10:43:00'),(27,5,8,5,'Som pesado de verdade, sem enrolação. Contratamos para o aniversário e todo mundo elogiou.','2026-04-23 15:00:00'),(28,13,9,5,'Rock, country e blues num set bem dosado. O material autoral caiu muito bem no nosso público.','2026-04-22 18:39:00'),(29,16,9,4,'Banda pontual, som preparado e preço justo. Recomendo para eventos da região.','2026-04-26 13:56:00'),(30,21,9,5,'Repertório variado agradou do jovem ao idoso. Ficou perfeito para a nossa festa.','2026-04-30 18:13:00'),(31,26,10,5,'Rock progressivo pesado e letras inteligentes. Finalista do João Rock não é à toa: é outro nível.','2026-04-29 11:52:00'),(32,31,10,5,'Dividiu o palco com Pitty e Nação Zumbi e entregou no nosso evento a mesma energia de festival grande.','2026-05-03 16:09:00'),(33,35,10,4,'Show impecável e profissionalismo do primeiro e-mail ao último acorde.','2026-05-07 11:26:00'),(34,1,10,5,'A entrega de som e a imagem do show estavam no nível de festival grande.','2026-05-11 16:43:00'),(35,1,11,5,'A voz dela no acústico foi de arrepiar. Trouxe Terça-feira e Epifania e a plateia pediu bis.','2026-05-06 14:05:00'),(36,2,11,5,'Cantora e compositora com repertório autoral de verdade. Foi o clima perfeito para a nossa noite.','2026-05-10 09:22:00'),(37,3,11,4,'Muito atenciosa no preparativo e pontual na apresentação. Recomendo de olhos fechados.','2026-05-14 14:39:00'),(38,4,12,5,'Pop rock com energia de EP novo. Tocou Silêncio e Odeio e o público cantou junto.','2026-05-13 17:18:00'),(39,5,12,4,'Atendimento rápido, show bem ensaiado e preço justo para o que entrega.','2026-05-17 12:35:00'),(40,13,12,5,'A voz tem pegada e a banda acompanha muito bem. Foi o melhor ato da nossa noite.','2026-05-21 17:52:00'),(41,16,13,5,'Pop punk com cara de banda nova que já sabe o que faz. Tocaram o álbum inteiro e ninguém sentou.','2026-05-20 10:31:00'),(42,21,13,5,'Desde 2022 e já com cinco singles e um EP no repertório: show maduro e divertido.','2026-05-24 15:48:00'),(43,26,13,4,'Pontualidade e comunicação exemplares. Ensaio antes do evento e apresentação redonda.','2026-05-28 10:05:00'),(44,31,13,5,'O novo álbum ao vivo soou ainda melhor que no estúdio. Público pediu bis.','2026-06-01 15:22:00'),(45,31,14,5,'Banda com história: começou no Festival do Brasil em 2004 e voltou ao palco com tudo. Valeu a espera.','2026-05-27 13:44:00'),(46,35,14,4,'O hiato de dez anos não deixou nenhum furo no som. Repertório bem ensaiado.','2026-05-31 18:01:00'),(47,1,14,5,'Os singles gravados com Adair Daufembach soaram ótimos ao vivo. Nosso público de Içara amou.','2026-06-04 13:18:00'),(48,2,15,5,'Vencedora do Prêmio Mundial Rock 2024 e isso se vê no palco: produção, som e presença.','2026-06-03 16:57:00'),(49,3,15,5,'Fez nosso festival parecer um show grande. Recomendo para quem quer seriedade na contratação.','2026-06-07 11:14:00'),(50,4,15,4,'Contratação simples, contrato cumprido e som entregue muito acima da média.','2026-06-11 16:31:00'),(51,5,16,5,'Guitarra e poesia: cantou Da Cor do Dia e TRETA e prendeu a atenção do início ao fim.','2026-06-10 09:10:00'),(52,13,16,5,'Artista do Selo Ambulante com voz e composição acima da média. Excelente custo-benefício.','2026-06-14 14:27:00'),(53,16,16,4,'Muito transparente na negociação e pontual. Repetimos o contrato sem pensar.','2026-06-18 09:44:00'),(54,21,16,4,'Guitarra bem resolvida e letra que prende. Boa conversa antes e depois do show.','2026-06-22 14:01:00'),(55,21,17,5,'Levou os clássicos de Queen e Bon Jovi com uma voz que rende arrepios. Três shows por semana se explica.','2026-06-17 12:23:00'),(56,26,17,4,'Banda afinada, repertório certeiro e muita animação. Nossa formatura ficou memorável.','2026-06-21 17:40:00'),(57,31,17,5,'Dez anos de estrada aparecem em cada acorde. Recomendo para casamentos e eventos grandes.','2026-06-25 12:57:00'),(58,35,18,5,'Resgatou os clássicos do pop e rock nacional com a energia que a Cocal Fest merecia.','2026-06-24 15:36:00'),(59,1,18,4,'Mais de uma década de shows se nota na segurança do palco. Repertório para todo gosto.','2026-06-28 10:53:00'),(60,2,18,5,'Banda versátil: tocou em casamento e em evento corporativo sem perder a essência.','2026-07-02 15:10:00'),(61,3,19,5,'Quarenta músicos tocando juntos: a abertura no Auditório Ruy Hülse foi emocionante.','2026-07-01 18:49:00'),(62,4,19,5,'Repertório clássico e popular bem escolhido, com regência convidada de primeira.','2026-07-05 13:06:00'),(63,5,19,4,'Produção cuidadosa e organização impecável do set até a recepção do público.','2026-07-09 18:23:00'),(64,13,19,5,'O acústico da Casa da Cultura ficou perfeito com a orquestra. Noite inesquecível.','2026-07-13 13:40:00'),(65,13,20,5,'Fotos que parecem de revista. A avaliação 5.0 que ele tem nas plataformas é merecida.','2026-07-08 11:02:00'),(66,16,20,5,'Cobriu casamento e formatura para nós com uma tranquilidade que acalma qualquer noiva.','2026-07-12 16:19:00'),(67,21,20,4,'Entrega rápida e contato próximo durante todo o evento. Recomendo para eventos em Criciúma.','2026-07-16 11:36:00'),(68,26,21,5,'Dupla que se complementa: um cuida da técnica e outro do instante. O material ficou lindo.','2026-07-15 14:15:00'),(69,31,21,4,'Desde 2007 dá para ver a experiência. Organização e entrega nota dez.','2026-07-19 09:32:00'),(70,35,21,5,'Marcaram cada detalhe do nosso casamento sem aparecer. Recomendo de olhos fechados.','2026-07-23 14:49:00'),(71,1,22,5,'Fotos premiadas não é exagero: as imagens do ensaio familiar ficaram de tirar o fôlego.','2026-07-22 17:28:00'),(72,2,22,5,'Trabalhou com calma e captou emoção em cada clique. Aprovado pela família inteira.','2026-07-26 12:45:00'),(73,3,22,4,'Prazo cumprido, pastas organizadas e preço honesto. Já indicamos para dois amigos.','2026-07-30 17:02:00'),(74,4,22,4,'Atendeu pedidos de última hora sem nenhum estresse. Entrega nota dez.','2026-08-03 12:19:00'),(75,4,23,5,'Vídeo institucional entregue com acabamento de TV. A agência ficou impressionada.','2026-07-29 10:41:00'),(76,5,23,4,'Desde 2011 dá para ver a experiência em motion graphics e corte. Prazo cumprido.','2026-08-02 15:58:00'),(77,13,23,5,'Briefing bem conduzido e entregas em etapas. Facilitou muito a nossa aprovação.','2026-08-06 10:15:00'),(78,16,24,5,'Documentário e vídeos de vendas com roteiro bem construído. O time comercial agradeceu.','2026-08-05 13:54:00'),(79,21,24,4,'As duas salas de podcast resolveram nossa necessidade de conteúdo em um dia só.','2026-08-09 18:11:00'),(80,26,24,5,'Criação ativa, não só execução. A marca ficou bem representada em cada peça entregue.','2026-08-13 13:28:00'),(81,35,25,5,'Cenas aéreas com drone e FPV que deram um upgrade gigantesco no nosso vídeo.','2026-08-12 16:07:00'),(82,1,25,5,'Nota 5.0 no Google com mais de dez avaliações: confirmamos na prática.','2026-08-16 11:24:00'),(83,2,25,4,'Voos seguros, licenciamento resolvido e imagem estável mesmo com vento.','2026-08-20 16:41:00'),(84,3,25,5,'O vídeo com drone abriu o material e já virou assunto na rede da empresa.','2026-08-24 11:58:00'),(85,2,26,5,'As alunas apresentaram ballet com uma disciplina que impressiona. Dá orgulho de assistir.','2026-08-19 09:20:00'),(86,3,26,5,'Estar na lista da Fundação Catarinense de Cultura não é para qualquer grupo, e isso se vê na técnica.','2026-08-23 14:37:00'),(87,4,26,4,'Ballet clássico e contemporâneo bem ensaiados, com direção atenciosa do início ao fim.','2026-08-27 09:54:00'),(88,5,27,5,'Danças italianas com tradição desde 1987. O grupo animou nossa festa junina sem igual.','2026-08-26 12:33:00'),(89,13,27,5,'Repertório pesquisado na Itália, com figurino e coreografia impecáveis.','2026-08-30 17:50:00'),(90,16,27,4,'Grupo bem organizado, com ensaios sérios e apresentação que encantou o bairro.','2026-09-03 12:07:00'),(91,21,28,5,'Ballet clássico com coreografia de Petipa levado com precisão. Ficou lindo no palco.','2026-09-02 15:46:00'),(92,26,28,5,'A Companhia trouxe repertório clássico e trabalhos solo com muita técnica.','2026-09-06 10:03:00'),(93,31,28,4,'Ensaios sérios e presença de palco marcante. Recomendo para mostras e eventos culturais.','2026-09-10 15:20:00'),(94,35,28,5,'O solo de dança clássica arrancou aplausos de pé. Direção impecável.','2026-09-14 10:37:00'),(95,1,29,5,'Identidade visual que traduziu a nossa marca em tudo: do cardápio ao feed.','2026-09-09 18:59:00'),(96,2,29,4,'Desde 2018 entrega trabalho consistente e com prazo cumprido.','2026-09-13 13:16:00'),(97,3,29,5,'Escuta atenta, duas rodadas de revisão e o resultado final aprovado de primeira.','2026-09-17 18:33:00'),(98,3,30,5,'Metodologia séria: escuta, pesquisa e análise. A nossa marca ficou com a cara que queríamos.','2026-09-16 11:12:00'),(99,4,30,4,'Fundado em 2020 e já com maturidade de estúdio grande. Entregou o manual da marca completo.','2026-09-20 16:29:00'),(100,5,30,5,'Comunicação clara e arquivos organizados. Recomendo para quem está começando a marca.','2026-09-24 11:46:00'),(101,13,31,5,'Mais de 20 anos de mercado aparecem na segurança das entregas. Logomarca aprovada de primeira.','2026-09-23 14:25:00'),(102,16,31,4,'O Gêmeos Design é referência em Criciúma e o atendimento segue no mesmo nível.','2026-09-27 09:42:00'),(103,21,31,5,'Catálogo e identidade visual entregues com prazo cumprido. Preço justo para a experiência.','2026-10-01 14:59:00'),(104,26,31,5,'Refizemos a identidade completa do negócio e a resposta do público foi imediata.','2026-10-05 09:16:00'),(105,7,32,5,'Mistura de eletrônica com metal que funciona. O single Fullmoon ao vivo é outro nível.','2026-09-30 17:38:00'),(106,8,32,5,'A 17 Sexta-Hard no Meteoro estúdio mostrou que a banda tem estrada e público fiel.','2026-10-04 12:55:00'),(107,13,32,4,'Som pesado bem resolvido e organização de palco acima do esperado para a cena independente.','2026-10-08 17:12:00');
/*!40000 ALTER TABLE `avaliacoes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-09 20:01:17

-- ============================================================
-- Origem: eventovivo_favoritos.sql
-- ============================================================
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: eventovivo
-- ------------------------------------------------------
-- Server version	5.5.20-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `favoritos`
--

DROP TABLE IF EXISTS `favoritos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favoritos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `evento_id` int(11) DEFAULT NULL,
  `freelancer_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_favorito_usuario` (`usuario_id`),
  KEY `fk_favorito_evento` (`evento_id`),
  KEY `fk_favorito_freelancer` (`freelancer_id`),
  CONSTRAINT `fk_favorito_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id_evento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_favorito_freelancer` FOREIGN KEY (`freelancer_id`) REFERENCES `freelancers` (`id_freelancer`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_favorito_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favoritos`
--

LOCK TABLES `favoritos` WRITE;
/*!40000 ALTER TABLE `favoritos` DISABLE KEYS */;
INSERT INTO `favoritos` VALUES (2,1,NULL,2),(3,2,5,NULL),(5,5,NULL,3);
/*!40000 ALTER TABLE `favoritos` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-09 20:01:19

-- ============================================================
-- Origem: eventovivo_mensagens.sql
-- ============================================================
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: eventovivo
-- ------------------------------------------------------
-- Server version	5.5.20-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `mensagens`
--

DROP TABLE IF EXISTS `mensagens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mensagens` (
  `id_mensagem` int(11) NOT NULL AUTO_INCREMENT,
  `remetente` int(11) NOT NULL,
  `destinatario` int(11) NOT NULL,
  `mensagem` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_envio` datetime DEFAULT NULL,
  PRIMARY KEY (`id_mensagem`),
  KEY `fk_mensagem_remetente` (`remetente`),
  KEY `fk_mensagem_destinatario` (`destinatario`),
  CONSTRAINT `fk_mensagem_destinatario` FOREIGN KEY (`destinatario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mensagem_remetente` FOREIGN KEY (`remetente`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensagens`
--

LOCK TABLES `mensagens` WRITE;
/*!40000 ALTER TABLE `mensagens` DISABLE KEYS */;
INSERT INTO `mensagens` VALUES (1,1,2,'Olá Ana! Gostaria de saber sobre sua disponibilidade para um evento.','2026-08-20 09:00:00'),(2,2,1,'Olá Lucas! Claro, posso verificar minha agenda. Qual seria a data?','2026-08-20 09:10:00'),(3,4,2,'Gostaríamos de convidá-la para participar do Festival de Música EventoVivo.','2026-08-20 10:00:00'),(4,3,4,'Tenho interesse em trabalhar como fotógrafo no próximo evento.','2026-08-20 10:30:00'),(5,2,4,'Obrigada pelo contato! Tenho interesse em participar do festival.','2026-08-20 11:00:00');
/*!40000 ALTER TABLE `mensagens` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-09 20:01:18

-- ============================================================
-- Origem: eventovivo_notificacoes.sql
-- ============================================================
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: eventovivo
-- ------------------------------------------------------
-- Server version	5.5.20-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `notificacoes`
--

DROP TABLE IF EXISTS `notificacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_remetente` int(11) NOT NULL,
  `usuario_destinatario` int(11) NOT NULL,
  `titulo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lida` tinyint(1) DEFAULT '0',
  `data_de_envio` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_notificacao_remetente` (`usuario_remetente`),
  KEY `fk_notificacao_destinatario` (`usuario_destinatario`),
  CONSTRAINT `fk_notificacao_destinatario` FOREIGN KEY (`usuario_destinatario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notificacao_remetente` FOREIGN KEY (`usuario_remetente`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificacoes`
--

LOCK TABLES `notificacoes` WRITE;
/*!40000 ALTER TABLE `notificacoes` DISABLE KEYS */;
INSERT INTO `notificacoes` VALUES (1,4,2,'Novo convite para evento',0,'2026-08-20 09:30:00'),(2,1,2,'Nova mensagem recebida',0,'2026-08-20 10:00:00'),(3,1,2,'Nova avaliação recebida',1,'2026-08-20 11:00:00'),(4,2,4,'Novo interesse em evento',0,'2026-08-20 12:00:00'),(5,4,3,'Novo evento publicado',1,'2026-08-20 12:30:00');
/*!40000 ALTER TABLE `notificacoes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-09 20:01:18

-- ============================================================
-- Origem: eventovivo_acesso_sistema.sql
-- ============================================================
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: eventovivo
-- ------------------------------------------------------
-- Server version	5.5.20-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `acesso_sistema`
--

DROP TABLE IF EXISTS `acesso_sistema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acesso_sistema` (
  `id_acesso` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `sessao` varchar(90) DEFAULT NULL,
  `pagina` varchar(200) DEFAULT NULL,
  `endereco_ip` varchar(45) DEFAULT NULL,
  `data_acesso` datetime NOT NULL,
  PRIMARY KEY (`id_acesso`),
  KEY `idx_acesso_data` (`data_acesso`),
  KEY `idx_acesso_usuario` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acesso_sistema`
--

LOCK TABLES `acesso_sistema` WRITE;
/*!40000 ALTER TABLE `acesso_sistema` DISABLE KEYS */;
INSERT INTO `acesso_sistema` VALUES (1,6,'106e5b1f2a8ba890eeb17e0a1143d5f5','PainelAdmin.php','127.0.0.1','2026-10-15 14:15:06'),(2,6,'5c4bedce8cbd8722f555a966df7efe2f','PainelAdmin.php','127.0.0.1','2026-10-15 14:15:08'),(3,6,'e2112b63658670496f1fbd09540f0984','PainelAdmin.php','127.0.0.1','2026-10-15 14:16:32'),(4,6,'b2a848127716440aa4f179025a58da91','PainelAdmin.php','127.0.0.1','2026-10-15 14:50:48'),(5,6,'9a9e8ea85b2c93c1f148b34b577da874','PainelAdmin.php','127.0.0.1','2026-10-15 14:51:25'),(6,6,'96e9cf2ec85d1e2b812af40e2db37fb2','PainelAdmin.php','127.0.0.1','2026-10-15 14:52:46'),(7,6,'143203596c6992a217f0d972fc480893','PainelAdmin.php','127.0.0.1','2026-10-15 14:52:49'),(8,6,'0d145dbc54e79ae0d2ed75e16ce95951','PainelAdmin.php','127.0.0.1','2026-10-15 14:52:51'),(9,6,'e5f4af23de961065ae04ce899d75b850','PainelAdmin.php','127.0.0.1','2026-10-15 14:52:53'),(10,6,'89c4eb600776a588b80a39fc83092d52','PainelAdmin.php','127.0.0.1','2026-10-15 14:52:55'),(11,6,'a6239f6a5158577aeaea51c261f226f3','PainelAdmin.php','127.0.0.1','2026-10-15 15:28:49'),(12,6,'bd88ff6825dbe0163227a803c19e09be','PainelAdmin.php','127.0.0.1','2026-10-15 15:28:51'),(13,6,'38dde39bab05390fd5792a44ff2d7b9d','PainelAdmin.php','127.0.0.1','2026-10-15 15:28:53'),(14,6,'458c55480332e2f58614c8dff2e44cb5','PainelAdmin.php','127.0.0.1','2026-10-15 15:28:55'),(15,6,'c6b499cfdbbd13f4ee5dda3b42494627','PainelAdmin.php','127.0.0.1','2026-10-15 15:28:57'),(16,6,'8d11319a5d3b6c20ab0185a83fadb0aa','PainelAdmin.php','127.0.0.1','2026-10-15 15:29:47'),(17,6,'826a1aac6bc45aa70d221bec0202c7d1','PainelAdmin.php','127.0.0.1','2026-10-15 15:43:58'),(18,6,'84a89610e48feae16d12e85fc017a2fd','PainelAdmin.php','127.0.0.1','2026-10-15 15:44:00'),(19,6,'f5cc111464bb6fc688ec8cd1d69b53ef','PainelAdmin.php','127.0.0.1','2026-10-15 15:44:02'),(20,6,'9eae6493da5facc1a23a27ddf4a07adf','PainelAdmin.php','127.0.0.1','2026-10-15 15:44:04'),(21,6,'5e2a2cb0aba573146ef55340b2e1424d','PainelAdmin.php','127.0.0.1','2026-10-15 15:44:07'),(22,6,'59ebba6c3fd839310ad7ba1ddd8fc0ca','PainelAdmin.php','127.0.0.1','2026-10-15 15:44:43'),(23,6,'ij3j7n46tgq36dblvgmgcgfs77','PainelAdmin.php','127.0.0.1','2026-10-29 17:04:32');
/*!40000 ALTER TABLE `acesso_sistema` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-09 20:01:18
