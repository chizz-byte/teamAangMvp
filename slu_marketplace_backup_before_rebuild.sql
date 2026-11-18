-- MySQL dump 10.13  Distrib 9.1.0, for Win64 (x86_64)
--
-- Host: localhost    Database: slu_marketplace
-- ------------------------------------------------------
-- Server version	9.1.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `listing_images`
--

DROP TABLE IF EXISTS `listing_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `listing_images` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `created_at` datetime(6) NOT NULL,
  `file_path` varchar(1000) NOT NULL,
  `sort_order` int NOT NULL,
  `listing_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FKq8bkplrutxrfur5u7pppml04y` (`listing_id`),
  CONSTRAINT `FKq8bkplrutxrfur5u7pppml04y` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `listing_images`
--

LOCK TABLES `listing_images` WRITE;
/*!40000 ALTER TABLE `listing_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `listing_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `listings`
--

DROP TABLE IF EXISTS `listings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `listings` (
  `listing_id` bigint NOT NULL AUTO_INCREMENT,
  `category` varchar(80) DEFAULT NULL,
  `item_condition` varchar(40) DEFAULT NULL,
  `course_code` varchar(80) DEFAULT NULL,
  `created_at` datetime(6) NOT NULL,
  `department` varchar(120) DEFAULT NULL,
  `description` varchar(4000) DEFAULT NULL,
  `edition` varchar(80) DEFAULT NULL,
  `image_path` varchar(1000) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `status` varchar(30) NOT NULL,
  `title` varchar(200) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `seller_id` bigint NOT NULL,
  PRIMARY KEY (`listing_id`),
  KEY `FKdfglshentvek3cnsr7a4nwh7j` (`seller_id`),
  CONSTRAINT `FKdfglshentvek3cnsr7a4nwh7j` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `listings`
--

LOCK TABLES `listings` WRITE;
/*!40000 ALTER TABLE `listings` DISABLE KEYS */;
INSERT INTO `listings` VALUES (1,'Textbooks','Like New','MATH 101','2026-11-18 20:01:14.159294','College of Arts and Sciences','Complete textbook with mild highlighting. Includes access code unused.','8th Edition','/images/book.svg',450.00,'Sold','Calculus Early Transcendentals 8th Edition','2026-11-18 20:01:14.341100',2),(2,'Reviewers','Good','ITE 28','2026-11-18 20:01:14.206963','College of Information Technology','Detailed reviewer for ITE 28 midterms and finals. Good for quick review.','2024','/images/reviewer.svg',600.00,'Available','Mobile Computing Reviewer','2026-11-18 20:01:14.206963',2),(3,'Laboratory Manuals','Good','ECE 201','2026-11-18 20:01:14.217953','College of Engineering','Official lab manual for ECE 201 with pre-lab guides and report templates.','3rd Edition','/images/lab.svg',350.00,'Available','Electronics Laboratory Manual','2026-11-18 20:01:14.217953',3),(4,'Uniforms and Academic Attire','Brand New','NUR 101','2026-11-18 20:01:14.225555','College of Nursing','Complete nursing uniform set, lightly used one semester. All pieces included.',NULL,'/images/uniform.svg',500.00,'Available','SLU Nursing Uniform Set','2026-11-18 20:01:14.225555',3),(5,'Course Materials','Good','IT 321','2026-11-18 20:01:14.240320','College of Information Technology','Comprehensive handwritten notes for IT 321 covering trees, graphs, and sorting.',NULL,'/images/notes.svg',150.00,'Available','Data Structures Handwritten Notes','2026-11-18 20:01:14.240320',4),(6,'Other Academic Items','Like New','ECE 105','2026-11-18 20:01:14.250366','College of Engineering','Complete drawing set with compass, protractor, triangles, and pencils.',NULL,'/images/other.svg',700.00,'Available','Engineering Drawing Instruments Kit','2026-11-18 20:01:14.250366',4);
/*!40000 ALTER TABLE `listings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `message_id` bigint NOT NULL AUTO_INCREMENT,
  `content` varchar(2000) NOT NULL,
  `timestamp` datetime(6) NOT NULL,
  `listing_id` bigint NOT NULL,
  `receiver_id` bigint NOT NULL,
  `sender_id` bigint NOT NULL,
  PRIMARY KEY (`message_id`),
  KEY `FKt05r0b6n0iis8u7dfna4xdh73` (`receiver_id`),
  KEY `FK4ui4nnwntodh6wjvck53dbk9m` (`sender_id`),
  KEY `FKq4pvtu9s5dw5igbibathqr8ea` (`listing_id`),
  CONSTRAINT `FK4ui4nnwntodh6wjvck53dbk9m` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `FKq4pvtu9s5dw5igbibathqr8ea` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`),
  CONSTRAINT `FKt05r0b6n0iis8u7dfna4xdh73` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (1,'Hi! Is the calculus book still available?','2026-11-18 20:01:14.293193',1,2,5),(2,'Yes, it is like new. I can do PHP 400 if you pick up at the Main Campus lobby.','2026-11-18 20:01:14.300552',1,5,2),(3,'Deal. I will be there after my 1 PM class. GCash is fine.','2026-11-18 20:01:14.314252',1,2,5);
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reports` (
  `report_id` bigint NOT NULL AUTO_INCREMENT,
  `created_at` datetime(6) NOT NULL,
  `reason` varchar(1000) NOT NULL,
  `status` varchar(30) NOT NULL,
  `listing_id` bigint NOT NULL,
  `reporter_id` bigint NOT NULL,
  PRIMARY KEY (`report_id`),
  KEY `FKd3qiw2om5d2oh5xb7fbdcq225` (`reporter_id`),
  KEY `FK7sds0ukyd61m6upcrdbt0xwmq` (`listing_id`),
  CONSTRAINT `FK7sds0ukyd61m6upcrdbt0xwmq` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`),
  CONSTRAINT `FKd3qiw2om5d2oh5xb7fbdcq225` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
INSERT INTO `reports` VALUES (1,'2026-11-18 20:01:14.391489','Price seems too good to be true. Please verify the notes are complete.','OPEN',5,5);
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `review_id` bigint NOT NULL AUTO_INCREMENT,
  `created_at` datetime(6) NOT NULL,
  `feedback` varchar(1000) DEFAULT NULL,
  `rating` int NOT NULL,
  `reviewed_user_id` bigint NOT NULL,
  `reviewer_id` bigint NOT NULL,
  `transaction_id` bigint NOT NULL,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `UKe5kxmugnxd4qawk2sxn9ky5qt` (`transaction_id`,`reviewer_id`),
  KEY `FK6m1kr6qopmdr9icvailpjx8xf` (`reviewed_user_id`),
  KEY `FKd1isgfajhtdl8mgg29up6mofi` (`reviewer_id`),
  CONSTRAINT `FK6m1kr6qopmdr9icvailpjx8xf` FOREIGN KEY (`reviewed_user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `FKd1isgfajhtdl8mgg29up6mofi` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `FKkaqv178xysaxtwl8vo0vyb9cs` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,'2026-11-18 20:01:14.371605','Smooth meetup and the book was exactly as described.',5,2,5,1),(2,'2026-11-18 20:01:14.384166','Paid on time and easy to coordinate with.',4,5,2,1);
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `transaction_id` bigint NOT NULL AUTO_INCREMENT,
  `agreed_price` decimal(12,2) NOT NULL,
  `completed_at` datetime(6) DEFAULT NULL,
  `created_at` datetime(6) NOT NULL,
  `meetup_location` varchar(120) DEFAULT NULL,
  `payment_method` varchar(80) DEFAULT NULL,
  `status` varchar(30) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `buyer_id` bigint NOT NULL,
  `listing_id` bigint NOT NULL,
  `seller_id` bigint NOT NULL,
  PRIMARY KEY (`transaction_id`),
  KEY `FKijk7ii2fvalv59schbb3e0bof` (`buyer_id`),
  KEY `FK50i2jege2ukupf1ynv0dq6eax` (`seller_id`),
  KEY `FKgpnxemro2596g2kplteoi6m01` (`listing_id`),
  CONSTRAINT `FK50i2jege2ukupf1ynv0dq6eax` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `FKgpnxemro2596g2kplteoi6m01` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`),
  CONSTRAINT `FKijk7ii2fvalv59schbb3e0bof` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,400.00,'2026-11-17 20:01:14.355280','2026-11-18 20:01:14.356297','SLU Main Campus','GCash','COMPLETED','2026-11-18 20:01:14.356297',5,1,2);
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` bigint NOT NULL AUTO_INCREMENT,
  `created_at` datetime(6) NOT NULL,
  `email` varchar(150) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `student_number` varchar(50) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `UK6th577uijqxjb16etmo0jciob` (`student_number`),
  UNIQUE KEY `UK6dotkott2kjsp8vw4d0m25fb7` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'2026-11-18 20:00:37.397720','admin@slu.edu.ph','System Administrator','$2a$10$3bTKMXmpJhdFVRKICzOEa.7NcxujikM.c3YJKN3t34CJbi6ozCkGq','ADMIN','ADM-0001'),(2,'2026-11-18 20:00:37.510716','maria.santos@slu.edu.ph','Maria Santos','$2a$10$3mA2Nyk1Et6Oc3.iS5UJ3uy9BjydNDH9CfZXqlYM8hMPc58k0Prum','STUDENT','2021-001'),(3,'2026-11-18 20:00:37.613213','jose.rizal@slu.edu.ph','Jose Rizal','$2a$10$EpTUGsivGGkhyz/Z6Upbg.zDFwVltt7uDmrUPtAsiRe2FaSfsipCy','STUDENT','2021-002'),(4,'2026-11-18 20:00:37.698262','ana.reyes@slu.edu.ph','Ana Reyes','$2a$10$E2BQiFeXmfC91J99YNLuUuI7MMMXDXMRHiEk9lTEOCyr62/kaQFV6','STUDENT','2022-014'),(5,'2026-11-18 20:00:37.783004','paolo.cruz@slu.edu.ph','Paolo Cruz','$2a$10$mh3exVyOP1gZovBMv0QZh.cOAaLY7RksGzCvpbLs7tHDHZ8qzJxia','STUDENT','2023-007');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-18 20:40:25
