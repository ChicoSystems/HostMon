-- MySQL dump 10.14  Distrib 5.5.47-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: hostmon
-- ------------------------------------------------------
-- Server version	5.5.47-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `active_devices`
--

DROP TABLE IF EXISTS `active_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `active_devices` (
  `deviceId` int(11) NOT NULL,
  UNIQUE KEY `deviceId` (`deviceId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `active_devices`
--

LOCK TABLES `active_devices` WRITE;
/*!40000 ALTER TABLE `active_devices` DISABLE KEYS */;
INSERT INTO `active_devices` VALUES (2),(3),(5),(6),(7),(9),(10),(38),(39),(40),(41),(42);
/*!40000 ALTER TABLE `active_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuration`
--

DROP TABLE IF EXISTS `configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `value` text NOT NULL,
  `description` text NOT NULL,
  `timeStamp` text NOT NULL COMMENT 'Used by some settings',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuration`
--

LOCK TABLES `configuration` WRITE;
/*!40000 ALTER TABLE `configuration` DISABLE KEYS */;
INSERT INTO `configuration` VALUES (1,'debug','0','Controls if the backend is in debug mode or not. In debug mode the console will display much more information.',''),(2,'averageGoalTime','10000','The time, in milliseconds, that we are aiming to have each record updated in. This will have an effect on the number of threads running in backend.',''),(3,'startingThreads','11','The number of threads the backend starts with. The thread number will change as the backend runs.',''),(4,'maxThreads','50','The maximum number of threads the backend will be able to run.',''),(5,'threadRemovalCoefficient','4','The Value that decides when threads are removed. The Lower the value the sooner an unneeded thread is removed.',''),(6,'threadAddCoefficient','10','The Value that decides when threads are added. The Higher the value the sooner a needed thread is added.',''),(7,'runPerThreadCheck','5','Every x amount of times a thread is run we check if we need to add or remove a thread.',''),(8,'numPingRunsBeforeDBRecord','20','The number of times we will ping before we make a call to the database.',''),(9,'minuteRecordAgeLimit','900000','The age each record in the minute table should get before being deleted. In Milliseconds.',''),(10,'hourRecordAgeLimit','14400000','The age each record in the hour table should get before being deleted. In Milliseconds.',''),(11,'dayRecordAgeLimit','345600000','The age each record in the day table should get before being deleted. In Milliseconds.',''),(12,'weekRecordAgeLimit','2419200000','The age each record in the week table should get before being deleted. In Milliseconds.',''),(13,'newestPingMinutes','300000','The amount of milliseconds we want to retrieve to average out new pings to add to hour table default was 5 minutes or 300000',''),(14,'newestPingHours','3600000','The amount of milliseconds we want to retrieve in order to average out pings to add to the day table default is 1 hour or 3600000 millis',''),(15,'newestPingDays','86400000','The amount of milliseconds we want to retrieve in order to average out pings to add to the day table default is 1 day or 86400000 millis',''),(16,'newestPingWeeks','604800000','The amount of milliseconds we want to retrieve in order to average out pings to add to the day table default is 1 week or 604800000 millis',''),(17,'backendRunning','true','Used by the java backend to declare it is running. Used by php-ajax to show the user if it is running.','1468096974'),(18,'installed','0','Lets us know if we are already installed or not.',''),(19,'yellowalarm','bleep.mp3','The Sound File played as an alarm','0'),(20,'redalarm','firePager.mp3','The Sound File played as an alarm','0');
/*!40000 ALTER TABLE `configuration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `day`
--

DROP TABLE IF EXISTS `day`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `day` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` text NOT NULL,
  `time` text NOT NULL,
  `latency` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33746 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `day`
--

LOCK TABLES `day` WRITE;
/*!40000 ALTER TABLE `day` DISABLE KEYS */;

INSERT INTO `day` VALUES (32756,'hotmail.com','1467739620216','637'),(32757,'stanford.edu','1467739627597','610'),(32758,'imgur.com','1467739628476','608'),(32759,'chicosystems.com','1467739627657','607');
/*!40000 ALTER TABLE `day` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` text NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devices`
--

LOCK TABLES `devices` WRITE;
/*!40000 ALTER TABLE `devices` DISABLE KEYS */;
INSERT INTO `devices` VALUES (1,'google.com','GOOGLE','I\'m pinging google'),(2,'facebook.com','FACEBOOK','This is facebook.'),(3,'reddit.com','REDDIT','this is reddit'),(4,'myspace.com','myspace','myspace is here'),(5,'gmail.com','Gmail Test','Development Test'),(6,'hotmail.com','HOTMAIL','Development Test'),(7,'digitalpath.net','DigitalPath','test'),(8,'digitalpath.com','DigitalPath INC.',''),(9,'chicosystems.com','Chico Systems LLC',''),(10,'news.com','NewsBoys',''),(11,'digg.com','Digg Stuff',''),(12,'earlhart.com','Earlhart Soap Works',''),(13,'plesk.com','PLESK',''),(14,'one.com','Uno One',''),(15,'two.com','Two 2',''),(16,'three.com','Three Men',''),(17,'four.com','Four Times',''),(18,'tiltservers.com','Tilt Servers LLC',''),(19,'tiltservers.net','Tilt Servers LLC 2',''),(20,'five.com','Fivers',''),(21,'six.com','Sixtus',''),(22,'seven.com','Se7en',''),(23,'7up.com','7up Soda',''),(24,'wargaming.net','UFC War Games',''),(25,'hbo.net','HBO Networks','HBO Television.'),(26,'he.net','Hurricane Electric','Probably the Fremont datacenter.'),(27,'ufcrewards.com','UFC Rewards','It\'s the UFC Rewards site.'),(28,'dishnetwork.net','Dish Networks',''),(29,'dishnetwork.com','Dish Networks 2',''),(30,'dish.net','Dish Networks 3',''),(31,'directtv.com','Direct Tv',''),(32,'bbc.co.uk','BBC News',''),(33,'foxnews.com','Fox News',''),(34,'speedtest.net','Speed Test Net.',''),(35,'cmich.edu','C.M.U.',''),(36,'evangel.edu','Evangel University',''),(37,'twitter.com','Twitter',''),(38,'stanford.edu','Stanford University',''),(39,'comcast.net','Comcast 1',''),(40,'imgur.com','Imgur',''),(41,'sparkfun.com','Sparkfun',''),(42,'youtube.com','Youtube',''),(43,'192.168.2.1','Router',''),(44,'184.21.246.255','Router 2','');
/*!40000 ALTER TABLE `devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hour`
--

DROP TABLE IF EXISTS `hour`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hour` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` text NOT NULL,
  `time` text NOT NULL,
  `latency` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=404727 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hour`
--

LOCK TABLES `hour` WRITE;
/*!40000 ALTER TABLE `hour` DISABLE KEYS */;
INSERT INTO `hour` VALUES (404151,'hotmail.com','1468082600444',736),(404152,'stanford.edu','1468082600135',675),(404153,'news.com','1468082602794',662),(404154,'imgur.com','1468082604611',615),(404155,'facebook.com','1468082599729',703),(404156,'sparkfun.com','1468082604570',659);

/*!40000 ALTER TABLE `hour` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `minute`
--

DROP TABLE IF EXISTS `minute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `minute` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` text NOT NULL,
  `time` text NOT NULL,
  `latency` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18784175 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `minute`
--

LOCK TABLES `minute` WRITE;
/*!40000 ALTER TABLE `minute` DISABLE KEYS */;
INSERT INTO `minute` VALUES (18782914,'reddit.com','1468096036406',664),(18782915,'reddit.com','1468096036758',630),(18782916,'reddit.com','1468096037135',652),(18782917,'comcast.net','1468096037309',663),(18782918,'digitalpath.net','1468096037618',654),(18782919,'chicosystems.com','1468096038005',662),(18782920,'hotmail.com','1468096038899',1374),(18782921,'imgur.com','1468096038908',1084),(18782922,'news.com','1468096038999',785);

/*!40000 ALTER TABLE `minute` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deviceID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `timestamp` text NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes`
--

LOCK TABLES `notes` WRITE;
/*!40000 ALTER TABLE `notes` DISABLE KEYS */;
INSERT INTO `notes` VALUES (1,12,1,'1407615812000','Customer has been experiencing intermittent problems with connecting. I\'d like us to monitor this for the day.'),(2,12,1,'1407616812000','This is continuing to have problems as of today.'),(3,12,1,'1407617812000','Customer went off line again at midnight. It looks like it could be maintenance work with their ISP.'),(4,12,2,'1407618812000','Logged in to test this just now. It\'s looking good as of this time.'),(6,12,1,'1407619912000','I called their ISP. Looks like they are on a wireless connection. Had their ISP do some rechanneling, latency went WAY WAY down.'),(7,12,2,'1407620912000','Nope, they called up again this morning, they are still having problems.'),(8,12,1,'1407622912000','I called back the customer.\r\nHad them turn their computer off and then back on again.\r\nEverything is working fine now. Why didn\'t we think of this before.'),(9,12,2,'1407624912000','That\'s genius. Let\'s monitor this for a little while longer. If it\'s good by tomorrow we can stop worring about it.'),(12,7,1,'1409374477842','first'),(13,12,1,'1409374558399','asdf'),(14,12,1,'1409443785653','sdfhyiikihk'),(15,12,1,'1409706238282','monitoring'),(16,5,1,'1409706511533','Start monitoring this now.'),(17,12,1,'1410211123271','notes'),(18,6,1,'1416160800360','test'),(19,6,1,'1416160911610','hi'),(20,6,1,'1416160957082','test2'),(23,12,1,'1416172578442','still watching this baby'),(24,5,1,'1416251550985','it\'s looking, really, pretty good.'),(25,12,1,'1416513233288','test'),(26,5,1,'1416549171847','this is a note'),(27,15,1,'1416681857689','Device is offline.'),(28,5,1,'1416804942284','test'),(29,2,1,'1467514889239','It\'s been a while since i tested this.'),(30,2,1,'1467864741162',''),(31,2,1,'1467864820991','test'),(32,2,1,'1467865103234','test'),(33,2,1,'1467865103234','test'),(34,2,1,'1467865328393','test'),(35,2,1,'1467865705159','test'),(36,2,1,'1467865933397','test'),(38,3,1,'1467912299642','test'),(39,2,1,'1467923485891','sar'),(40,2,1,'1467924168554',''),(41,2,1,'1467924223824','test'),(42,2,1,'1467924418688','test'),(43,2,1,'1467924667193','test'),(44,2,1,'1467924721294','test'),(45,2,1,'1467924884305','test'),(46,2,1,'1467924913237','test'),(47,2,1,'1467924952222','test'),(48,2,1,'1467925029675','test'),(49,2,1,'1467925979079','test'),(51,2,1,'1467931171669','test');
/*!40000 ALTER TABLE `notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `notify_if_over_latency` int(11) NOT NULL,
  `notify_after_time_seconds` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
INSERT INTO `subscriptions` VALUES (1,1,1,250,60);
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timers`
--

DROP TABLE IF EXISTS `timers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timers`
--

LOCK TABLES `timers` WRITE;
/*!40000 ALTER TABLE `timers` DISABLE KEYS */;
INSERT INTO `timers` VALUES (3,'fiveMinuteTimer','1468097172417'),(4,'fifteenMinuteTimer','1468097662295'),(5,'hourTimer','1468098508075'),(6,'twelveHourTimer','1468125493037'),(7,'dayTimer','1468125491671'),(8,'fourtyEightHourTimer','1468211890593'),(9,'weekTimer','1468119503645');
/*!40000 ALTER TABLE `timers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usr` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `email` text NOT NULL,
  `subscriptions` text NOT NULL,
  `admin_level` int(1) NOT NULL,
  `pass` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `regIP` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `dt` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'itravers','isaac.a.travers@gmail.com','',10,'8b047144fb28065ed57512f8ab89eca5','','0000-00-00 00:00:00'),(2,'test','test@test.com','',0,'098f6bcd4621d373cade4e832627b4f6','','0000-00-00 00:00:00'),(3,'slack','confusedvirtuoso@gmail.com','',10,'8b047144fb28065ed57512f8ab89eca5','','0000-00-00 00:00:00'),(4,'test2','test2@test2.com','',9,'098f6bcd4621d373cade4e832627b4f6','','0000-00-00 00:00:00'),(14,'test3','','',10,'098f6bcd4621d373cade4e832627b4f6','','0000-00-00 00:00:00'),(15,'zenrix','','',2,'8b047144fb28065ed57512f8ab89eca5','','0000-00-00 00:00:00'),(29,'admin','','',10,'8b047144fb28065ed57512f8ab89eca5','','0000-00-00 00:00:00'),(30,'test33','','',0,'9cb45d54b2ccdc1c486e2f3eb87fbe9f','','0000-00-00 00:00:00'),(31,'test22','','',1,'4d42bf9c18cb04139f918ff0ae68f8a0','','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `week`
--

DROP TABLE IF EXISTS `week`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `week` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` text NOT NULL,
  `time` text NOT NULL,
  `latency` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1579 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `week`
--

LOCK TABLES `week` WRITE;
/*!40000 ALTER TABLE `week` DISABLE KEYS */;
INSERT INTO `week` VALUES (1497,'hotmail.com','1467556040389','635'),(1498,'imgur.com','1467556045060','608'),(1499,'stanford.edu','1467556039062','610'),(1500,'chicosystems.com','1467556043287','622'),(1501,'gmail.com','1467556051012','605'),(1502,'earlhart.com','1467556032874','0'),(1503,'youtube.com','1467556044940','603'),(1504,'reddit.com','1467556044073','616'),(1505,'news.com','1467556042108','617'),(1506,'sparkfun.com','1467556036682','604'),(1507,'facebook.com','1467556042512','640'),(1508,'digitalpath.net','1467556043634','636'),(1509,'comcast.net','1467556042906','612'),(1510,'184.21.246.255','1467556051147','0'),(1511,'hotmail.com','1467642462059','644'),(1512,'stanford.edu','1467642464202','614'),(1513,'imgur.com','1467642462433','613'),(1514,'chicosystems.com','1467642464137','618'),(1515,'gmail.com','1467642466739','605'),(1516,'earlhart.com','1467642462544','0'),(1517,'reddit.com','1467642466601','617'),(1518,'youtube.com','1467642466306','602'),(1519,'news.com','1467642465543','619'),(1520,'sparkfun.com','1467642464457','605'),(1521,'facebook.com','1467642466064','646'),(1522,'digitalpath.net','1467642464715','637'),(1523,'comcast.net','1467642464611','609'),(1524,'184.21.246.255','1467642463458','0'),(1525,'hotmail.com','1467728811535','642'),(1526,'imgur.com','1467728817807','613'),(1527,'stanford.edu','1467728817457','613'),(1528,'chicosystems.com','1467728817377','616'),(1529,'gmail.com','1467728821715','617'),(1530,'earlhart.com','1467728810438','0');

/*!40000 ALTER TABLE `week` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `year`
--

DROP TABLE IF EXISTS `year`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `year` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` text NOT NULL,
  `time` text NOT NULL,
  `latency` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=268 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `year`
--

LOCK TABLES `year` WRITE;
/*!40000 ALTER TABLE `year` DISABLE KEYS */;
INSERT INTO `year` VALUES (1,'digitalpath.com','1406417036175','641'),(2,'news.com','1406417037999','644'),(3,'hbo.com','1406417035147','-1'),(4,'fightnight.com','1406417038667','-1'),(5,'news.net','1406417039821','-1'),(6,'myspace.com','1406417034978','629'),(7,'hello.org','1406417039670','-1'),(8,'gmail.com','1406417037402','635'),(9,'xnxx.com','1406417035256','-1'),(10,'hello.com','1406417040902','-1'),(11,'xhamster.com','1406417034031','731'),(12,'facebook.com','1406417037169','671'),(13,'microsoft.com','1406417046164','-1'),(14,'hello.net','1406417041824','-1'),(15,'reddit.com','1406417036153','644'),(16,'google.com','1406417038137','641'),(17,'new.com','1406417036866','643'),(18,'w3schools.com','1406417029249','-1'),(19,'hotmail.com','1406417037392','654'),(20,'youtube.com','1406417038539','633'),(21,'new.net','1406417037151','-1'),(22,'digitalpath.com','1406684655800','327'),(23,'digg.com','1406685749251','-1'),(24,'news.com','1406683492877','293'),(25,'three.com','1406686835458','456'),(26,'myspace.com','1406683486579','285'),(27,'news.net','1406423892758','-1'),(28,'plesk.com','1406686126902','336'),(29,'tiltservers.com','1406686126496','279'),(30,'digitalpath.net','1406684711868','338'),(31,'facebook.com','1406683483074','345');

/*!40000 ALTER TABLE `year` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-07-09 13:42:54
