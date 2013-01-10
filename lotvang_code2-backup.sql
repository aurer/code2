# ************************************************************
# Sequel Pro SQL dump
# Version 3408
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.1.50)
# Database: lotvang_code2
# Generation Time: 2011-11-29 22:43:30 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table tags
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tags`;

CREATE TABLE `tags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `clip_id` int(11) NOT NULL,
  `category` varchar(128) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;

INSERT INTO `tags` (`id`, `title`, `clip_id`, `category`, `active`, `created`, `updated`)
VALUES
	(28,'Rivendell',13,'language',1,'2011-09-20 22:58:13','2011-11-29 22:13:50'),
	(29,'Rivendell',14,'language',1,'2011-09-20 22:58:13','2011-11-29 22:13:50'),
	(30,'Javascript',10,'language',1,'2011-09-12 23:38:41','2011-11-29 22:17:09'),
	(31,'Jquery',10,'framework',1,'2011-09-14 22:06:02','2011-11-29 22:17:36'),
	(34,'Rivendell',12,'language',1,'2011-09-20 22:58:13','2011-11-29 22:13:50'),
	(35,'SQL',16,'language',1,'2011-09-14 22:05:51','2011-11-29 22:17:16'),
	(36,'Rivendell',17,'language',1,'2011-09-20 22:58:13','2011-11-29 22:13:50'),
	(39,'Rivendell',19,'language',1,'2011-09-20 22:58:13','2011-11-29 22:13:50'),
	(51,'SQL',39,'language',1,'2011-09-29 00:04:02','2011-11-29 22:17:16'),
	(55,'TAG',36,'general',1,'2011-09-30 22:34:46','2011-11-29 22:17:17'),
	(56,'JSP',40,'language',1,'2011-09-30 22:35:18','2011-11-29 22:17:13'),
	(57,'XML',41,'language',1,'2011-09-30 22:35:44','2011-11-29 22:17:27'),
	(58,'Rivendell',43,'language',1,'2011-09-30 22:36:20','2011-11-29 22:13:50'),
	(65,'HTML',47,'language',1,'2011-10-17 21:11:20','2011-11-29 22:17:03'),
	(66,'PHP',15,'language',1,'2011-10-17 21:59:24','2011-11-29 22:17:15'),
	(67,'CMS',15,'general',1,'2011-10-17 21:59:24','2011-11-29 22:16:56'),
	(72,'HTML',20,'language',1,'2011-10-24 23:57:54','2011-11-29 22:17:03'),
	(74,'Jquery',11,'framework',1,'2011-11-03 23:27:07','2011-11-29 22:17:36'),
	(75,'AJAX',11,'language',1,'2011-11-03 23:27:07','2011-11-29 22:16:47'),
	(83,'Javascript',46,'language',1,'0000-00-00 00:00:00','2011-11-29 22:17:09'),
	(84,'Color',46,'general',1,'0000-00-00 00:00:00','2011-11-29 22:16:58'),
	(85,'SQL',38,'language',1,'0000-00-00 00:00:00','2011-11-29 22:17:16'),
	(86,'Jquery',27,'framework',1,'0000-00-00 00:00:00','2011-11-29 22:17:36'),
	(87,'PHP',32,'language',1,'0000-00-00 00:00:00','2011-11-29 22:17:15'),
	(88,'CSS',45,'language',1,'0000-00-00 00:00:00','2011-11-29 22:17:00'),
	(89,'CSS3',45,'framework',1,'0000-00-00 00:00:00','2011-11-29 22:20:15'),
	(96,'crap',56,NULL,0,'2011-11-26 00:22:00','2011-11-29 22:19:44'),
	(98,'PHP',57,'language',0,'2011-11-28 22:46:41','2011-11-29 22:17:15'),
	(99,'HTML',58,'language',0,'2011-11-28 23:03:52','2011-11-29 22:17:03'),
	(100,'iluh',59,'general',0,'2011-11-28 23:13:29','2011-11-29 22:19:46'),
	(101,'XML',18,'language',1,'0000-00-00 00:00:00','2011-11-29 22:17:27'),
	(102,'Xanadu',48,'language',1,'0000-00-00 00:00:00','2011-11-29 22:20:18'),
	(103,'Weird',48,'general',1,'0000-00-00 00:00:00','2011-11-29 22:26:03');

/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
