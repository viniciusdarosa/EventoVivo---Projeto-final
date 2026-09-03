-- MySQL dump 10.13  Distrib 8.0.34, for Win64 (x86_64)
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
  PRIMARY KEY (`id_evento`),
  KEY `fk_evento_usuario` (`usuario_id`),
  KEY `fk_evento_categoria` (`categoria_id`),
  CONSTRAINT `fk_evento_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_evento_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_eventos` (`id_categoria`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos`
--

LOCK TABLES `eventos` WRITE;
/*!40000 ALTER TABLE `eventos` DISABLE KEYS */;
INSERT INTO `eventos` VALUES (1,4,1,'Festival de Música EventoVivo','Festival reunindo artistas locais, bandas e cantores da região.','2026-09-12','2026-09-12','18:00:00','23:30:00','Parque das Nações','100','Criciúma','SC','88804-000',25.00,500,'festival_musica.jpg','Livre','2026-08-20 10:00:00'),(2,4,2,'Noite de Teatro','Apresentação de artistas locais em uma noite dedicada ao teatro.','2026-09-20','2026-09-20','19:30:00','22:00:00','Teatro Municipal','200','Criciúma','SC','88805-000',30.00,250,'teatro.jpg','12','2026-08-20 10:30:00'),(3,4,3,'Festival de Dança Sul','Evento com apresentações de grupos e dançarinos da região.','2026-10-05','2026-10-05','15:00:00','21:00:00','Centro Cultural','350','Içara','SC','88820-100',20.00,300,'danca.jpg','Livre','2026-08-20 11:00:00'),(4,4,4,'Feira Cultural Regional','Feira com artistas, artesãos, apresentações musicais e exposições.','2026-10-15','2026-10-17','10:00:00','22:00:00','Praça Central','50','Criciúma','SC','88806-000',0.00,1000,'feira_cultural.jpg','Livre','2026-08-20 11:30:00'),(5,4,5,'Festival de Primavera','Festival com música ao vivo, apresentações artísticas e gastronomia.','2026-11-07','2026-11-08','14:00:00','23:00:00','Parque Centenário','500','Criciúma','SC','88807-000',35.00,800,'festival_primavera.jpg','Livre','2026-08-20 12:00:00');
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

-- Dump completed on 2026-08-20 16:55:35
