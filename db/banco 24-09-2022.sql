-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           8.0.18 - MySQL Community Server - GPL
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Copiando estrutura do banco de dados para salao
CREATE DATABASE IF NOT EXISTS `salao` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `salao`;

-- Copiando estrutura para tabela salao.agenda
CREATE TABLE IF NOT EXISTS `agenda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_servico` int(11) NOT NULL,
  `data` date NOT NULL,
  `hora` time NOT NULL,
  `nome` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `celular` varchar(50) COLLATE utf8_unicode_ci DEFAULT '',
  `cpf` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_agenda_servicos` (`id_servico`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Copiando dados para a tabela salao.agenda: 0 rows
/*!40000 ALTER TABLE `agenda` DISABLE KEYS */;
INSERT IGNORE INTO `agenda` (`id`, `id_servico`, `data`, `hora`, `nome`, `email`, `celular`, `cpf`) VALUES
	(1, 1, '2022-09-24', '08:30:00', 'WILLAS', NULL, '', NULL),
	(2, 1, '2022-09-24', '09:00:00', 'PAULO', NULL, '', NULL),
	(3, 1, '2022-09-24', '09:30:00', 'JARBAS', NULL, '', NULL),
	(4, 2, '2022-09-24', '10:00:00', 'LUCAS', NULL, '', NULL),
	(5, 3, '2022-09-24', '10:30:00', 'LEANDRO', NULL, '', NULL),
	(6, 2, '2022-09-24', '11:30:00', 'JADSON', NULL, '', NULL),
	(7, 3, '2022-09-24', '12:00:00', 'DANIEL', NULL, '', NULL);
/*!40000 ALTER TABLE `agenda` ENABLE KEYS */;

-- Copiando estrutura para tabela salao.servicos
CREATE TABLE IF NOT EXISTS `servicos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Copiando dados para a tabela salao.servicos: 0 rows
/*!40000 ALTER TABLE `servicos` DISABLE KEYS */;
INSERT IGNORE INTO `servicos` (`id`, `descricao`) VALUES
	(1, 'CABELO'),
	(2, 'BARBA'),
	(3, 'CABELO E BARBA');
/*!40000 ALTER TABLE `servicos` ENABLE KEYS */;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
