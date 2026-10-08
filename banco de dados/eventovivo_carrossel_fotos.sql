-- MySQL dump 10.13  Distrib 5.5.20, for Win32 (x86)
--
-- Host: localhost    Database: eventovivo
-- ------------------------------------------------------
-- Server version	5.5.20

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
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
CREATE TABLE `carrossel_fotos` (
  `id_foto` int(11) NOT NULL AUTO_INCREMENT,
  `freelancer_id` int(11) NOT NULL,
  `imagem` varchar(255) NOT NULL,
  `legenda` varchar(255) DEFAULT NULL,
  `data_cadastro` datetime DEFAULT NULL,
  PRIMARY KEY (`id_foto`),
  KEY `fk_carrossel_freelancer` (`freelancer_id`),
  CONSTRAINT `fk_carrossel_freelancer` FOREIGN KEY (`freelancer_id`) REFERENCES `freelancers` (`id_freelancer`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carrossel_fotos`
--

LOCK TABLES `carrossel_fotos` WRITE;
/*!40000 ALTER TABLE `carrossel_fotos` DISABLE KEYS */;
INSERT INTO `carrossel_fotos` VALUES (1,1,'portfolio_lucas.png','Apresentação musical realizada em evento cultural.','2026-10-08 00:00:01'),(2,2,'portfolio_ana.png','Apresentação da cantora Ana Beatriz em festival regional.','2026-10-08 00:00:02'),(3,3,'portfolio_marcos.png','Fotografia profissional realizada durante um show ao vivo.','2026-10-08 00:00:03'),(4,4,'portfolio_gabriel.png','Apresentação de dança urbana em evento cultural.','2026-10-08 00:00:04'),(5,5,'portfolio_eventossul.png','Identidade visual criada para divulgação de festival musical.','2026-10-08 00:00:05');
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

-- Dump completed on 2026-10-08
