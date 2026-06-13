-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: fisiogest
-- ------------------------------------------------------
-- Server version	8.0.43

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
-- Table structure for table `asignaciones_equipo`
--

DROP TABLE IF EXISTS `asignaciones_equipo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asignaciones_equipo` (
  `id_asignaciones` bigint unsigned NOT NULL AUTO_INCREMENT,
  `paciente_id` bigint unsigned NOT NULL,
  `inventario_id` bigint unsigned NOT NULL,
  `fecha_asignacion` date NOT NULL,
  `fecha_devolucion` date DEFAULT NULL,
  `estado` enum('activo','devuelto') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `notas` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_asignaciones`),
  KEY `asignaciones_equipo_paciente_id_foreign` (`paciente_id`),
  KEY `asignaciones_equipo_inventario_id_foreign` (`inventario_id`),
  CONSTRAINT `asignaciones_equipo_inventario_id_foreign` FOREIGN KEY (`inventario_id`) REFERENCES `inventario` (`id_inventario`) ON DELETE CASCADE,
  CONSTRAINT `asignaciones_equipo_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`paciente_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignaciones_equipo`
--

LOCK TABLES `asignaciones_equipo` WRITE;
/*!40000 ALTER TABLE `asignaciones_equipo` DISABLE KEYS */;
INSERT INTO `asignaciones_equipo` VALUES (1,3,1,'2026-06-03','2026-06-03','devuelto',NULL,'2026-06-03 08:59:57','2026-06-03 09:04:37'),(2,5,2,'2026-06-03','2026-06-03','devuelto',NULL,'2026-06-03 09:00:05','2026-06-03 09:00:10'),(3,3,5,'2026-06-03','2026-06-03','devuelto',NULL,'2026-06-03 09:00:20','2026-06-03 09:04:28'),(4,3,2,'2026-06-03','2026-06-03','devuelto',NULL,'2026-06-03 09:01:06','2026-06-03 09:04:40'),(5,3,1,'2026-06-03','2026-06-03','devuelto',NULL,'2026-06-03 09:04:44','2026-06-03 09:19:37'),(6,5,2,'2026-06-03','2026-06-03','devuelto',NULL,'2026-06-03 09:04:52','2026-06-03 09:19:40'),(7,3,1,'2026-06-03',NULL,'activo',NULL,'2026-06-03 09:19:59','2026-06-03 09:19:59'),(8,5,1,'2026-06-03','2026-06-03','devuelto',NULL,'2026-06-03 09:28:52','2026-06-03 09:29:19'),(9,5,5,'2026-06-03',NULL,'activo',NULL,'2026-06-03 09:33:04','2026-06-03 09:33:04'),(10,3,2,'2026-06-03',NULL,'activo',NULL,'2026-06-03 09:37:09','2026-06-03 09:37:09'),(11,4,5,'2026-06-03','2026-06-03','devuelto',NULL,'2026-06-03 09:40:51','2026-06-03 09:41:47'),(12,6,2,'2026-06-03','2026-06-03','devuelto',NULL,'2026-06-03 09:41:13','2026-06-03 09:41:43'),(13,6,2,'2026-06-03',NULL,'activo',NULL,'2026-06-03 09:50:10','2026-06-03 09:50:10'),(14,4,4,'2026-06-03',NULL,'activo',NULL,'2026-06-03 10:21:31','2026-06-03 10:21:31'),(15,1,3,'2026-06-03','2026-06-03','devuelto',NULL,'2026-06-03 11:14:19','2026-06-03 11:46:33'),(16,7,6,'2026-06-03','2026-06-03','devuelto',NULL,'2026-06-03 11:46:42','2026-06-03 13:37:04'),(17,10,7,'2026-06-03',NULL,'activo',NULL,'2026-06-03 11:47:09','2026-06-03 11:47:09'),(18,11,8,'2026-06-03','2026-06-03','devuelto',NULL,'2026-06-03 11:47:21','2026-06-03 12:11:28'),(19,11,8,'2026-06-03',NULL,'activo',NULL,'2026-06-03 11:48:23','2026-06-03 11:48:23'),(20,6,8,'2026-06-03',NULL,'activo',NULL,'2026-06-03 11:49:17','2026-06-03 11:49:17'),(21,9,8,'2026-06-03',NULL,'activo',NULL,'2026-06-03 12:03:16','2026-06-03 12:03:16'),(22,9,8,'2026-06-03',NULL,'activo',NULL,'2026-06-03 12:04:14','2026-06-03 12:04:14'),(23,10,5,'2026-06-03',NULL,'activo',NULL,'2026-06-03 13:37:23','2026-06-03 13:37:23'),(24,3,5,'2026-06-03','2026-06-03','devuelto',NULL,'2026-06-03 13:43:22','2026-06-03 13:43:35');
/*!40000 ALTER TABLE `asignaciones_equipo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citas`
--

DROP TABLE IF EXISTS `citas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `citas` (
  `cita_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `paciente_id` bigint unsigned NOT NULL,
  `fisioterapeuta_id` bigint unsigned NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `motivo` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('programada','atendida','cancelada','reprogramada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'programada',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`cita_id`),
  KEY `citas_paciente_id_foreign` (`paciente_id`),
  KEY `citas_fisioterapeuta_id_foreign` (`fisioterapeuta_id`),
  CONSTRAINT `citas_fisioterapeuta_id_foreign` FOREIGN KEY (`fisioterapeuta_id`) REFERENCES `fisioterapeutas` (`fisioterapeuta_id`) ON DELETE CASCADE,
  CONSTRAINT `citas_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`paciente_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citas`
--

LOCK TABLES `citas` WRITE;
/*!40000 ALTER TABLE `citas` DISABLE KEYS */;
INSERT INTO `citas` VALUES (1,1,2,'2026-08-06 12:30:00','Terapia de rehabilitación','programada',NULL,'2026-05-31 03:46:09','2026-06-03 08:16:25'),(2,1,2,'2024-06-27 11:30:00','Ejercicios de rehabilitación','atendida',NULL,'2026-05-31 08:11:42','2026-06-03 08:16:40'),(4,3,6,'2026-06-01 10:30:00','Terapia de rehabilitación','atendida',NULL,'2026-05-31 11:23:47','2026-06-03 13:56:23'),(5,4,1,'2026-05-31 13:00:00','Terapia de Rehabilitación','atendida',NULL,'2026-05-31 11:26:33','2026-06-03 13:56:01'),(7,5,6,'2026-05-30 23:45:00','Terapia por esguince en tobillo derecho','atendida',NULL,'2026-05-31 11:40:05','2026-05-31 11:46:13'),(8,1,2,'2026-06-01 13:00:00','Terapia de rehabilitación','atendida',NULL,'2026-05-31 11:50:38','2026-06-03 13:56:45'),(9,5,6,'2026-05-31 10:00:00','Terapia de rehabilitación','atendida',NULL,'2026-05-31 12:06:53','2026-06-03 13:55:53'),(10,6,1,'2026-06-01 13:00:00','Cita de seguimiento esguince en tobillo derecho','atendida',NULL,'2026-05-31 12:11:59','2026-06-03 13:56:11'),(12,11,3,'2026-06-12 15:00:00','Seguimiento tratamiento de lesión de rodilla','programada',NULL,'2026-06-03 11:21:59','2026-06-03 11:21:59'),(13,8,3,'2026-06-10 10:00:00','Tendinitis','programada',NULL,'2026-06-03 11:22:47','2026-06-03 11:22:47'),(14,10,3,'2026-06-08 10:30:00','Lesión de tendón','programada',NULL,'2026-06-03 11:23:36','2026-06-03 11:23:36'),(15,7,3,'2026-06-02 23:45:00','Lesión de muslo izquierdo','cancelada',NULL,'2026-06-03 11:24:24','2026-06-03 13:56:33'),(16,9,8,'2026-06-17 09:30:00','Golpe de cadera','programada',NULL,'2026-06-03 11:53:25','2026-06-03 11:53:25'),(17,4,2,'2026-06-03 10:30:00','Esguince en tobillo izquierdo','programada',NULL,'2026-06-03 13:27:05','2026-06-03 13:27:05'),(18,7,3,'2026-06-03 12:30:00','evaluacion: Lesión de femur izquierdo','programada',NULL,'2026-06-03 13:38:48','2026-06-03 14:03:36'),(19,5,6,'2026-06-03 11:30:00','terapia: Lesión en tendón de mano izquierda','programada',NULL,'2026-06-03 13:45:17','2026-06-03 13:45:17'),(20,9,1,'2026-03-06 09:00:00','Terapia de rehabilitación','programada',NULL,'2026-06-03 14:17:06','2026-06-03 14:17:06'),(21,1,1,'2026-03-06 09:00:00','Rehabilitación','programada',NULL,'2026-06-03 14:18:44','2026-06-03 14:18:44'),(22,8,8,'2026-06-03 10:00:00','Rehabilitación','programada',NULL,'2026-06-03 14:26:38','2026-06-03 14:26:38'),(23,11,9,'2026-06-03 09:00:00','Dedos torcidos','programada',NULL,'2026-06-03 14:28:37','2026-06-03 14:28:37'),(24,10,3,'2026-06-03 11:30:00','Hueso roto','programada',NULL,'2026-06-03 14:30:13','2026-06-03 14:30:13');
/*!40000 ALTER TABLE `citas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fisioterapeutas`
--

DROP TABLE IF EXISTS `fisioterapeutas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fisioterapeutas` (
  `fisioterapeuta_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint unsigned NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `especialidad` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `horario` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`fisioterapeuta_id`),
  KEY `fisioterapeutas_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `fisioterapeutas_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`usuario_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fisioterapeutas`
--

LOCK TABLES `fisioterapeutas` WRITE;
/*!40000 ALTER TABLE `fisioterapeutas` DISABLE KEYS */;
INSERT INTO `fisioterapeutas` VALUES (1,2,'Maribel','Uribe','Traumatología',NULL,1,'{\"Lunes\": {\"fin\": \"18:00\", \"activo\": true, \"inicio\": \"10:00\"}, \"Jueves\": {\"fin\": \"15:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Martes\": {\"fin\": \"14:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Sábado\": {\"fin\": \"13:00\", \"activo\": false, \"inicio\": \"09:00\"}, \"Viernes\": {\"fin\": \"18:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Miércoles\": {\"fin\": \"16:00\", \"activo\": true, \"inicio\": \"10:00\"}}','2026-05-31 03:42:12','2026-06-03 12:46:01'),(2,3,'Estefani','Aparicio','Deportiva',NULL,1,'{\"Lunes\": {\"fin\": \"14:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Jueves\": {\"fin\": \"14:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Martes\": {\"fin\": \"17:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Sábado\": {\"fin\": \"13:00\", \"activo\": false, \"inicio\": \"09:00\"}, \"Viernes\": {\"fin\": \"16:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Miércoles\": {\"fin\": \"16:00\", \"activo\": true, \"inicio\": \"09:00\"}}','2026-05-31 03:42:12','2026-06-03 12:45:33'),(3,4,'Erick','Urtado','Deportiva',NULL,1,'{\"Lunes\": {\"fin\": \"14:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Jueves\": {\"fin\": \"16:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Martes\": {\"fin\": \"16:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Sábado\": {\"fin\": \"13:00\", \"activo\": false, \"inicio\": \"09:00\"}, \"Viernes\": {\"fin\": \"16:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Miércoles\": {\"fin\": \"14:00\", \"activo\": true, \"inicio\": \"09:00\"}}','2026-05-31 03:42:12','2026-06-03 12:48:21'),(6,1,'Alejandro','Vargas','Deportiva',NULL,1,'{\"Lunes\": {\"fin\": \"16:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Jueves\": {\"fin\": \"16:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Martes\": {\"fin\": \"14:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Sábado\": {\"fin\": \"13:00\", \"activo\": false, \"inicio\": \"09:00\"}, \"Viernes\": {\"fin\": \"15:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Miércoles\": {\"fin\": \"15:00\", \"activo\": true, \"inicio\": \"09:00\"}}','2026-05-31 10:00:45','2026-06-03 12:48:38'),(8,1,'Maribel',' Guardia','Traumatología','76451234',1,'{\"Lunes\": {\"fin\": \"14:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Jueves\": {\"fin\": \"15:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Martes\": {\"fin\": \"15:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Sábado\": {\"fin\": \"13:00\", \"activo\": false, \"inicio\": \"09:00\"}, \"Viernes\": {\"fin\": \"16:00\", \"activo\": true, \"inicio\": \"09:00\"}, \"Miércoles\": {\"fin\": \"14:00\", \"activo\": true, \"inicio\": \"09:00\"}}','2026-06-03 11:51:15','2026-06-03 12:48:52'),(9,1,'Ada','Chiflis','Rehabilitación','76452312',1,NULL,'2026-06-03 11:52:06','2026-06-03 11:52:06'),(10,1,'La','tia Bubu','Neurología','74567890',1,NULL,'2026-06-03 11:52:27','2026-06-03 11:52:27');
/*!40000 ALTER TABLE `fisioterapeutas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventario`
--

DROP TABLE IF EXISTS `inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventario` (
  `id_inventario` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `marca` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modelo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('disponible','en_uso','mantenimiento','baja') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disponible',
  `cantidad` smallint NOT NULL DEFAULT '1',
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_inventario`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventario`
--

LOCK TABLES `inventario` WRITE;
/*!40000 ALTER TABLE `inventario` DISABLE KEYS */;
INSERT INTO `inventario` VALUES (1,'Prótesis de Rodilla A1','Prótesis',NULL,'X-200','disponible',20,NULL,'2026-05-31 03:42:12','2026-06-03 10:20:09'),(2,'Cinta Elástica Roja','Material Clínico',NULL,'Rojo','baja',25,NULL,'2026-05-31 03:42:12','2026-06-03 10:20:29'),(3,'Ultrasonido Terapéutico','Electroterapia','Chattanooga','U1','en_uso',5,NULL,'2026-05-31 03:42:12','2026-05-31 03:42:12'),(4,'Camilla de Tratamiento','Equipo de Rehabilitación','Hausmann','4700','disponible',8,NULL,'2026-05-31 03:42:12','2026-05-31 03:42:12'),(5,'Bicicleta Estática','Equipo de Rehabilitación','NuStep','T5','baja',2,NULL,'2026-05-31 03:42:12','2026-05-31 03:42:12'),(6,'Electoestimulador TENS','Electroterapia','Compex','Sport Elite','disponible',4,NULL,'2026-05-31 03:42:12','2026-05-31 03:42:12'),(7,'Pelota de Rehabilitación','Material Clínico','Gymnic','65cm','disponible',10,NULL,'2026-05-31 03:42:12','2026-05-31 03:42:12'),(8,'Silla de ruedas','Equipo de Rehabilitación',NULL,'Stalman','disponible',25,NULL,'2026-06-03 08:20:33','2026-06-03 08:20:33'),(9,'Pelota de Rehabilitación','Material Clínico',NULL,'65cm','en_uso',10,NULL,'2026-06-03 08:22:48','2026-06-03 08:22:48');
/*!40000 ALTER TABLE `inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2024_01_01_000001_create_usuarios_table',1),(5,'2024_01_01_000002_create_fisioterapeutas_table',1),(6,'2024_01_01_000003_create_pacientes_table',1),(7,'2024_01_01_000004_create_inventario_table',1),(8,'2024_01_01_000005_create_sesiones_asignaciones_citas_tables',1),(9,'2026_05_18_014520_create_personal_access_tokens_table',1),(10,'2026_05_18_022731_add_rol_to_users_table',1),(11,'2026_05_31_050217_add_fisioterapeuta_id_to_usuarios_table',2),(12,'2026_06_03_000001_add_horario_to_fisioterapeutas_table',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pacientes`
--

DROP TABLE IF EXISTS `pacientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pacientes` (
  `paciente_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint unsigned NOT NULL,
  `fisioterapeuta_id` bigint unsigned DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `genero` enum('masculino','femenino','otro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `diagnostico` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`paciente_id`),
  KEY `pacientes_usuario_id_foreign` (`usuario_id`),
  KEY `pacientes_fisioterapeuta_id_foreign` (`fisioterapeuta_id`),
  CONSTRAINT `pacientes_fisioterapeuta_id_foreign` FOREIGN KEY (`fisioterapeuta_id`) REFERENCES `fisioterapeutas` (`fisioterapeuta_id`) ON DELETE SET NULL,
  CONSTRAINT `pacientes_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`usuario_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pacientes`
--

LOCK TABLES `pacientes` WRITE;
/*!40000 ALTER TABLE `pacientes` DISABLE KEYS */;
INSERT INTO `pacientes` VALUES (1,1,3,'Eduardo','Maldonado','1965-12-04','masculino','72254312',NULL,NULL,'2026-05-31 03:45:20','2026-06-03 08:18:08'),(3,1,6,'Ariela','Caceres','1985-12-08','femenino','76542319',NULL,NULL,'2026-05-31 10:42:29','2026-05-31 11:38:36'),(4,1,1,'Tenchis','Celiber','1982-10-10','femenino','67453215',NULL,NULL,'2026-05-31 11:25:39','2026-05-31 11:25:39'),(5,1,6,'Eduardo','Ochoa','2001-12-08','masculino','76543289',NULL,NULL,'2026-05-31 11:39:18','2026-05-31 11:39:18'),(6,1,1,'Osmin','Rubio','1989-09-15','masculino','78654312',NULL,NULL,'2026-05-31 12:11:09','2026-05-31 12:11:09'),(7,1,NULL,'Oscar','Ortiz','1960-02-13','masculino','76891234',NULL,NULL,'2026-06-03 11:17:18','2026-06-03 11:17:18'),(8,1,NULL,'Claudia','Ortiz','1982-07-14','femenino','72456789',NULL,NULL,'2026-06-03 11:18:07','2026-06-03 11:18:07'),(9,1,NULL,'Osiris','Meza','1985-05-23','masculino','72456789',NULL,NULL,'2026-06-03 11:18:47','2026-06-03 11:18:47'),(10,1,3,'William','Aguilar','1975-08-26','masculino','74561209',NULL,NULL,'2026-06-03 11:19:37','2026-06-03 11:19:58'),(11,1,6,'Donald','Trumpet','1965-09-19','masculino','76893421',NULL,NULL,'2026-06-03 11:21:03','2026-06-03 11:21:03');
/*!40000 ALTER TABLE `pacientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesiones_terapia`
--

DROP TABLE IF EXISTS `sesiones_terapia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sesiones_terapia` (
  `id_sesion_terapia` bigint unsigned NOT NULL AUTO_INCREMENT,
  `paciente_id` bigint unsigned NOT NULL,
  `fisioterapeuta_id` bigint unsigned NOT NULL,
  `fecha` date NOT NULL,
  `duracion_minutos` smallint NOT NULL DEFAULT '60',
  `tipo_terapia` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `evolucion` enum('mejora','estable','retroceso') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'estable',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_sesion_terapia`),
  KEY `sesiones_terapia_paciente_id_foreign` (`paciente_id`),
  KEY `sesiones_terapia_fisioterapeuta_id_foreign` (`fisioterapeuta_id`),
  CONSTRAINT `sesiones_terapia_fisioterapeuta_id_foreign` FOREIGN KEY (`fisioterapeuta_id`) REFERENCES `fisioterapeutas` (`fisioterapeuta_id`) ON DELETE CASCADE,
  CONSTRAINT `sesiones_terapia_paciente_id_foreign` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`paciente_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones_terapia`
--

LOCK TABLES `sesiones_terapia` WRITE;
/*!40000 ALTER TABLE `sesiones_terapia` DISABLE KEYS */;
/*!40000 ALTER TABLE `sesiones_terapia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('2M5uJn9duvednp1yCtUuU9AmlBNYy8O070XcMyIt',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiejZnTEtuenA0Yk10T2NDTm1seUhkNk0zSG1xZk51dHpCYWZ5ZXNrUCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo3OiJ3ZWxjb21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1780474775),('43p9HJpZSiBZowE2LMfSAGjFjl1RVmnbzGFk7F3Y',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNDRNY0JyZDFyNXl2ZXZ6dU9wUk1JWjRlaGdOSHFXTTJrandBMGlJNSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo3OiJ3ZWxjb21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1780425500),('sNZkDxVdX9EguBHbPpkilwF1j7DykXzcsN2lpbHl',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidkpLQURlS2NrOGljMGNHR09CUTlScEliaVp1TnV0SkRCQ0VsZWJ6VCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo3OiJ3ZWxjb21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1780286541),('TsV2prKkRCRAHBOXUgDpNrRHOjx31wixzDaLja4K',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.122.1 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidzJHemhBRHFpNjlhbjhYejZrbW9pM1drTUIzbGF4NFFmSWlYd0JzNSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo3OiJ3ZWxjb21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1780470496),('UbsTQD6sbawnc3ZOXCGFBsbdy2Ptq3Lu6jkOCVJv',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.122.1 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNjJFNDh4N3N1R3YzWHVCcTZFanBOOTllbXQxRWIzbmdzclYzU3ZaeCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo3OiJ3ZWxjb21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1780425494),('zhGeRBTvA776SU9PmD9znAzrcD7dyOzle7IdbF2q',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.122.1 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoic0NHaUZPU3NqaDgyZmRCRTlZVExybHpEUG9EOFFWZWhzcmZMRm00VyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo3OiJ3ZWxjb21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1780286764);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'paciente',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `usuario_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('admin','fisioterapeuta','paciente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'paciente',
  `fisioterapeuta_id` bigint unsigned DEFAULT NULL,
  `correo_verificado_en` timestamp NULL DEFAULT NULL,
  `recordar_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`usuario_id`),
  UNIQUE KEY `usuarios_correo_unique` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Administrador','admin@fisiogest.com','$2y$12$rc7qZCGWRVX.ZY1US8XsK.LqfdQDBw9IE/9RaXTmFfCKsTTfXWF.O','admin',NULL,NULL,NULL,'2026-05-31 03:42:10','2026-05-31 03:42:10'),(2,'Manrivel Gorado','manrivel@fisiogest.com','$2y$12$wqP2C4Lui5xwqlqnhCm00.4yYcfahT3bif2/5ZuMUNmWkrzMn/fyC','fisioterapeuta',NULL,NULL,NULL,'2026-05-31 03:42:10','2026-05-31 03:42:10'),(3,'Barvis Raten','barvis@fisiogest.com','$2y$12$E0eBucJZDiwvZRrkRF4lJOH3ie.24FqoabVTiNRTHEJ/h7W8.5xn2','fisioterapeuta',NULL,NULL,NULL,'2026-05-31 03:42:10','2026-05-31 03:42:10'),(4,'Bardena Drides','bardena@fisiogest.com','$2y$12$TJedpmN0RLAQJWAx79bE..3BjH3lQ9hBuhUK6K5BatS9h2dlVbKLq','fisioterapeuta',NULL,NULL,NULL,'2026-05-31 03:42:11','2026-05-31 03:42:11'),(5,'Marina Gomez','marina@fisiogest.com','$2y$12$N8/AiotAB4Yq6FlGddCCEeNctmtG7edUfoisuWfSrhibRcEVKGgp.','fisioterapeuta',4,NULL,NULL,'2026-05-31 03:42:11','2026-05-31 03:42:11'),(6,'Retmen Nones','retmen@fisiogest.com','$2y$12$iR0Xj12jpXJa4eZdy7pS4.MJbnSLZFLNqCHilZlBoET9YNcTagbCm','fisioterapeuta',NULL,NULL,NULL,'2026-05-31 03:42:12','2026-05-31 03:42:12'),(7,'Alejandro Vargas','alejandro@fisiogest.com','$2y$12$NuRoJH1YzGQxYw4zHsmy1uHEpW7OeIxaCcf9tvEFRfEOd94S3LDh6','fisioterapeuta',6,NULL,NULL,'2026-05-31 09:36:53','2026-05-31 09:36:53'),(8,'Maribel Uribe','maribel@fisiogest.com','$2y$12$Kp2LzFdikQ2KHq8TZAY1ROZWfrC/m/fg.UoabKf7wl89nS64tpsBK','fisioterapeuta',1,NULL,NULL,'2026-05-31 10:52:08','2026-05-31 10:52:08'),(9,'Estefany Aparicio','estefany@fisiogest.com','$2y$12$tM/kemJQtTDJu8MxFPPr4.VejjiMWsbNQyTX95/A7ZzBmGusJpOKq','fisioterapeuta',2,NULL,NULL,'2026-06-03 10:56:59','2026-06-03 10:56:59'),(11,'Erick Urtado','erick@fisiogest.com','$2y$12$y/hANir3Hvdaz69btqUtUugijJGP6nEYptkCnaai.f1ilVTXyOsji','fisioterapeuta',3,NULL,NULL,'2026-06-03 11:39:05','2026-06-03 11:39:05'),(12,'Maribel Guardia','guardiamaribel@fisiogest.com','$2y$12$Azd6RA.L3rqMlYJVC/aCeupd36rcYZ69ib6DfC5T0Jgh5bRShmS7C','fisioterapeuta',8,NULL,NULL,'2026-06-03 11:57:09','2026-06-03 11:57:09');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-03 16:23:48
