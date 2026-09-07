/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.15-MariaDB, for FreeBSD14.3 (amd64)
--
-- Host: dumbsql.db    Database: deltaruneboards
-- ------------------------------------------------------
-- Server version	12.3.2-MariaDB-ubu2404

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
-- Table structure for table `smf_admin_info_files`
--

DROP TABLE IF EXISTS `smf_admin_info_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_admin_info_files` (
  `id_file` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `path` varchar(255) NOT NULL DEFAULT '',
  `parameters` varchar(255) NOT NULL DEFAULT '',
  `data` text NOT NULL,
  `filetype` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_file`),
  KEY `idx_filename` (`filename`(30))
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_approval_queue`
--

DROP TABLE IF EXISTS `smf_approval_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_approval_queue` (
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `id_attach` int(10) unsigned NOT NULL DEFAULT 0,
  `id_event` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_categories`
--

DROP TABLE IF EXISTS `smf_arcade_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_categories` (
  `id_cat` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(255) DEFAULT '',
  `num_games` int(10) unsigned DEFAULT 0,
  `cat_order` int(10) unsigned DEFAULT 1,
  `special` int(10) unsigned DEFAULT 0,
  `member_groups` varchar(255) DEFAULT '-2,-1,0,2',
  `cat_icon` varchar(255) DEFAULT '',
  `cat_dl` int(10) unsigned DEFAULT 0,
  `cat_js` int(10) unsigned DEFAULT 0,
  PRIMARY KEY (`id_cat`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_arcade_categories`
--

LOCK TABLES `smf_arcade_categories` WRITE;
/*!40000 ALTER TABLE `smf_arcade_categories` DISABLE KEYS */;
INSERT INTO `smf_arcade_categories` VALUES
(1,'Default',0,1,0,'-2,-1,0,1,2','Default.gif',0,0);
/*!40000 ALTER TABLE `smf_arcade_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_arcade_favorite`
--

DROP TABLE IF EXISTS `smf_arcade_favorite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_favorite` (
  `id_favorite` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_member` int(10) unsigned DEFAULT NULL,
  `id_game` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_favorite`),
  KEY `id_game` (`id_game`,`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_files`
--

DROP TABLE IF EXISTS `smf_arcade_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_files` (
  `id_file` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_game` int(10) unsigned DEFAULT NULL,
  `file_type` varchar(255) DEFAULT 'game',
  `game_name` varchar(255) DEFAULT '',
  `status` int(10) unsigned DEFAULT 0,
  `game_file` varchar(255) DEFAULT '',
  `game_directory` varchar(255) DEFAULT '',
  `submit_system` varchar(255) DEFAULT '',
  PRIMARY KEY (`id_file`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_game_info`
--

DROP TABLE IF EXISTS `smf_arcade_game_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_game_info` (
  `id_game` int(11) NOT NULL,
  `icon_position` int(11) DEFAULT 0,
  `icon_position_hide` int(11) DEFAULT 0,
  PRIMARY KEY (`id_game`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_games`
--

DROP TABLE IF EXISTS `smf_arcade_games`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_games` (
  `id_game` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `internal_name` varchar(255) DEFAULT '',
  `game_name` varchar(255) DEFAULT '',
  `game_file` varchar(255) DEFAULT '',
  `game_directory` varchar(255) DEFAULT '',
  `description` text DEFAULT NULL,
  `help` text DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT '',
  `thumbnail_small` varchar(255) DEFAULT '',
  `cover_icon` varchar(255) DEFAULT '',
  `submit_system` varchar(255) DEFAULT '',
  `rom_system` varchar(255) DEFAULT 'none',
  `id_cat` int(10) unsigned DEFAULT 0,
  `enabled` int(10) unsigned DEFAULT 0,
  `download` int(10) unsigned DEFAULT 1,
  `local_permissions` int(10) unsigned DEFAULT 0,
  `score_type` tinyint(3) unsigned DEFAULT 0,
  `js_insertion` tinyint(3) unsigned DEFAULT 2,
  `member_groups` varchar(255) DEFAULT '-2,-1,0,2',
  `game_rating` float DEFAULT 0,
  `id_champion` int(10) unsigned DEFAULT 0,
  `id_champion_score` int(10) unsigned DEFAULT 0,
  `extra_data` text DEFAULT NULL,
  `num_plays` int(10) unsigned DEFAULT 0,
  `num_rates` int(10) unsigned DEFAULT 0,
  `num_favorites` int(10) unsigned DEFAULT 0,
  `id_topic` int(10) unsigned DEFAULT 0,
  `rom_flag` int(10) unsigned DEFAULT 0,
  PRIMARY KEY (`id_game`),
  UNIQUE KEY `internal_name` (`internal_name`(191))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_guest_data`
--

DROP TABLE IF EXISTS `smf_arcade_guest_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_guest_data` (
  `online_ip` varchar(255) NOT NULL DEFAULT '',
  `online_time` int(10) unsigned DEFAULT 0,
  `show_online` int(10) unsigned DEFAULT 1,
  `current_action` int(10) unsigned DEFAULT 0,
  `current_game` int(10) unsigned DEFAULT 0,
  `game_section` int(10) unsigned DEFAULT 0,
  `temp_name` varchar(255) DEFAULT '',
  PRIMARY KEY (`online_ip`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_guest_extra_data`
--

DROP TABLE IF EXISTS `smf_arcade_guest_extra_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_guest_extra_data` (
  `online_ip` varchar(255) NOT NULL DEFAULT '',
  `year` int(10) unsigned DEFAULT 0,
  `day` int(10) unsigned DEFAULT 0,
  `count` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`online_ip`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_internal_game_conflicts`
--

DROP TABLE IF EXISTS `smf_arcade_internal_game_conflicts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_internal_game_conflicts` (
  `id_conflict` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_game` int(11) DEFAULT NULL,
  `internal_id_conflict` int(11) DEFAULT 0,
  `internal_name_conflict` varchar(255) DEFAULT '',
  `conflict_directory` varchar(255) DEFAULT '',
  `submit_system_flag` int(11) DEFAULT 0,
  PRIMARY KEY (`id_conflict`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_lists`
--

DROP TABLE IF EXISTS `smf_arcade_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_lists` (
  `id_list` int(11) NOT NULL AUTO_INCREMENT,
  `list_name` varchar(255) NOT NULL,
  `list_source_file` varchar(255) NOT NULL,
  `list_function` varchar(255) NOT NULL,
  `admin_function` varchar(255) NOT NULL,
  `lang_function` varchar(255) NOT NULL,
  `list_template` varchar(255) NOT NULL,
  `enabled` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_list`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_arcade_lists`
--

LOCK TABLES `smf_arcade_lists` WRITE;
/*!40000 ALTER TABLE `smf_arcade_lists` DISABLE KEYS */;
INSERT INTO `smf_arcade_lists` VALUES
(1,'Retro','','','','','ArcadeSkinListB',1),
(2,'Vintage','','','','','ArcadeSkinListA',1),
(3,'Generic','','','','','ArcadeList',1);
/*!40000 ALTER TABLE `smf_arcade_lists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_arcade_matches`
--

DROP TABLE IF EXISTS `smf_arcade_matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_matches` (
  `id_match` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '',
  `id_member` int(10) unsigned DEFAULT 0,
  `private_game` int(10) unsigned DEFAULT 0,
  `status` int(10) unsigned DEFAULT 0,
  `created` int(10) unsigned DEFAULT 0,
  `updated` int(10) unsigned DEFAULT 0,
  `num_players` int(10) unsigned DEFAULT 2,
  `current_players` int(10) unsigned DEFAULT 1,
  `num_rounds` int(10) unsigned DEFAULT 1,
  `current_round` int(10) unsigned DEFAULT 0,
  `match_data` text DEFAULT NULL,
  PRIMARY KEY (`id_match`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_matches_players`
--

DROP TABLE IF EXISTS `smf_arcade_matches_players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_matches_players` (
  `id_match` int(10) unsigned NOT NULL,
  `id_member` int(10) unsigned NOT NULL,
  `status` int(10) unsigned DEFAULT 0,
  `score` int(10) unsigned DEFAULT 0,
  `player_data` text DEFAULT NULL,
  PRIMARY KEY (`id_match`,`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_matches_results`
--

DROP TABLE IF EXISTS `smf_arcade_matches_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_matches_results` (
  `id_match` int(10) unsigned NOT NULL,
  `id_member` int(10) unsigned NOT NULL,
  `round` int(10) unsigned NOT NULL DEFAULT 0,
  `score` float DEFAULT 0,
  `duration` float DEFAULT NULL,
  `end_time` int(10) unsigned DEFAULT 0,
  `score_status` varchar(255) DEFAULT '',
  `validate_hash` varchar(255) DEFAULT '',
  PRIMARY KEY (`id_match`,`id_member`,`round`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_matches_rounds`
--

DROP TABLE IF EXISTS `smf_arcade_matches_rounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_matches_rounds` (
  `id_match` int(10) unsigned NOT NULL,
  `round` int(10) unsigned NOT NULL DEFAULT 0,
  `id_game` int(10) unsigned DEFAULT NULL,
  `status` int(10) unsigned DEFAULT 0,
  PRIMARY KEY (`id_match`,`round`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_member_data`
--

DROP TABLE IF EXISTS `smf_arcade_member_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_member_data` (
  `id_member` int(10) unsigned NOT NULL,
  `online_ip` varchar(255) DEFAULT '',
  `online_time` int(10) unsigned DEFAULT 0,
  `show_online` int(10) unsigned DEFAULT 0,
  `online_name` varchar(255) DEFAULT '',
  `online_color` varchar(255) DEFAULT '',
  `current_action` int(10) unsigned DEFAULT 0,
  `current_game` int(10) unsigned DEFAULT 0,
  `game_section` int(10) unsigned DEFAULT 0,
  PRIMARY KEY (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_membergroups`
--

DROP TABLE IF EXISTS `smf_arcade_membergroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_membergroups` (
  `id_group` int(11) NOT NULL DEFAULT -2,
  `download_limit` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_members`
--

DROP TABLE IF EXISTS `smf_arcade_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_members` (
  `id_member` int(10) unsigned NOT NULL DEFAULT 0,
  `arena_invite` int(10) unsigned NOT NULL DEFAULT 0,
  `arena_match_end` int(10) unsigned NOT NULL DEFAULT 0,
  `arena_new_round` int(10) unsigned NOT NULL DEFAULT 0,
  `new_game` int(10) unsigned NOT NULL DEFAULT 0,
  `champion_email` int(10) unsigned NOT NULL DEFAULT 0,
  `champion_pm` int(10) unsigned NOT NULL DEFAULT 0,
  `notify_game_reports` int(10) unsigned NOT NULL DEFAULT 0,
  `games_per_page` int(10) unsigned NOT NULL DEFAULT 0,
  `archive_type` int(10) unsigned NOT NULL DEFAULT 0,
  `arcade_gametype` int(10) unsigned NOT NULL DEFAULT 0,
  `arcade_gametype_rom` int(10) unsigned NOT NULL DEFAULT 0,
  `arcade_emulatorjs_rom` text NOT NULL,
  `new_champion_any` int(10) unsigned NOT NULL DEFAULT 0,
  `new_champion_own` int(10) unsigned NOT NULL DEFAULT 0,
  `scores_per_page` int(10) unsigned NOT NULL DEFAULT 0,
  `skin` int(10) unsigned NOT NULL DEFAULT 0,
  `list` int(10) unsigned NOT NULL DEFAULT 0,
  `skin_rom` int(10) unsigned NOT NULL DEFAULT 0,
  `list_rom` int(10) unsigned NOT NULL DEFAULT 0,
  `skin_mobile` int(10) unsigned NOT NULL DEFAULT 0,
  `list_mobile` int(10) unsigned NOT NULL DEFAULT 0,
  `detect_mobile` int(10) unsigned NOT NULL DEFAULT 0,
  `download_count` int(10) unsigned DEFAULT 0,
  PRIMARY KEY (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_mobile_lists`
--

DROP TABLE IF EXISTS `smf_arcade_mobile_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_mobile_lists` (
  `id_list` int(11) NOT NULL AUTO_INCREMENT,
  `list_name` varchar(255) NOT NULL,
  `list_source_file` varchar(255) NOT NULL,
  `list_function` varchar(255) NOT NULL,
  `admin_function` varchar(255) NOT NULL,
  `lang_function` varchar(255) NOT NULL,
  `list_template` varchar(255) NOT NULL,
  `enabled` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_list`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_arcade_mobile_lists`
--

LOCK TABLES `smf_arcade_mobile_lists` WRITE;
/*!40000 ALTER TABLE `smf_arcade_mobile_lists` DISABLE KEYS */;
INSERT INTO `smf_arcade_mobile_lists` VALUES
(1,'arcade_list_mobile_generic','','','','','ArcadeListMobileGeneric',1),
(2,'arcade_list_mobile0','','','','','ArcadeListMobileLight',1);
/*!40000 ALTER TABLE `smf_arcade_mobile_lists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_arcade_mobile_skins`
--

DROP TABLE IF EXISTS `smf_arcade_mobile_skins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_mobile_skins` (
  `id_skin` int(11) NOT NULL AUTO_INCREMENT,
  `skin_name` varchar(255) NOT NULL,
  `skin_source_file` varchar(255) NOT NULL,
  `skin_function` varchar(255) NOT NULL,
  `admin_function` varchar(255) NOT NULL,
  `lang_function` varchar(255) NOT NULL,
  `skin_template` varchar(255) NOT NULL,
  `enabled` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_skin`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_arcade_mobile_skins`
--

LOCK TABLES `smf_arcade_mobile_skins` WRITE;
/*!40000 ALTER TABLE `smf_arcade_mobile_skins` DISABLE KEYS */;
INSERT INTO `smf_arcade_mobile_skins` VALUES
(1,'arcade_skin_mobile_generic','','','','','ArcadeSkinMobileGeneric',1),
(2,'arcade_skin_mobile0','','','','','ArcadeSkinMobileLight',1);
/*!40000 ALTER TABLE `smf_arcade_mobile_skins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_arcade_modsettings`
--

DROP TABLE IF EXISTS `smf_arcade_modsettings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_modsettings` (
  `variable` varchar(255) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`variable`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_arcade_modsettings`
--

LOCK TABLES `smf_arcade_modsettings` WRITE;
/*!40000 ALTER TABLE `smf_arcade_modsettings` DISABLE KEYS */;
INSERT INTO `smf_arcade_modsettings` VALUES
('arcadeButtonPlacement','search'),
('arcadeCheckLevel','1'),
('arcadeCommentLen','75'),
('arcadeCommonGameTypes','v1game|v2game|v3arcade|silver|phpbb|mochi|custom_game|ibp|ibp2|ibp3|ibp32|html5|html52|html53|rom'),
('arcadeCookieEncryptionCipher','e84f479edff3927ff6da9783e39782a75307954ba43785e93be7e73dcad2'),
('arcadeCronBiosSchedule','1'),
('arcadeDailyCronTasks','1788668125'),
('arcadeDescriptLength','2000'),
('arcadeDisableArchive','1'),
('arcadeDisplayRomType','1'),
('arcadeDisplayType','1'),
('arcadeEmulatorJSVersion','2010.01.01'),
('arcadeEmulatorJS_GithubLink','https://github.com/EmulatorJS/EmulatorJS/archive/refs/tags/v4.2.1.zip'),
('arcadeEmulatorJS_WebDevCoresLink','https://usc1.contabostorage.com/2b82b691fd3a4b538e795440917f6907:shared/Arcade_EmulatorJS_Cores.zip'),
('arcadeEmulatorJS_WebDevLink','https://usc1.contabostorage.com/2b82b691fd3a4b538e795440917f6907:shared/Arcade_EmulatorJS.zip'),
('arcadeEnabled','1'),
('arcadeEnableFavorites','1'),
('arcadeEnableRatings','1'),
('arcadeGamecacheUpdate','1'),
('arcadeGamesNameLengthB','100'),
('arcadeList','2'),
('arcadeListSort','3'),
('arcadePermissionMode','1'),
('arcadeProfileView','1'),
('arcadeRandomIdVar','11111111111111111111'),
('arcadeRecurrentCronTasks','1788668125'),
('arcadeRetroLibraryCode','noneprovided'),
('arcadeRomArchiveFileTypes','zip|7z'),
('arcadeRomGameTypes','zip|7z|gd3|gd7|dx2|bsx|cgb|ss|gdi|scd|sgg|al|a52|dat|lst|pcfx|mame|bin|adf|adz|dms|fdi|ipf|raw|hdf|hdz|lha|slave|info|cue|ccd|chd|nrg|mds|iso|uae|m3u|nds|fds|nes|unif|unf|gb|gbc|dmg|col|cv|rom|mdx|md|smd|gen|bms|sms|gg|sg|68k|sgd|lnx|ngp|ngc|pce|img|toc|exe|pbp|ws|wsc|pc2|vb|vboy|gba|n64|v64|z64|u1|ndd|mdf|cbn|32x|elf|cso|prx|a78|smc|sfc|swc|fig|bs|st|a26|d64|d6z|d71|d7z|d80|d81|d82|d8z|g64|g6z|g41|g4z|x64|x6z|nib|nbz|d2m|d4m|t64|tap|tcrt|prg|p00|crt|cmd|vfl|vsf|gz|20|40|60|a0|b0|j64|jag|abs|cof|psp'),
('arcadeRomIconRemoteDict','https://usc1.contabostorage.com/2b82b691fd3a4b538e795440917f6907:shared/smf_arcade_shared_files/wordDictionary.txt'),
('arcadeRomIconRemoteIcons','https://usc1.contabostorage.com/2b82b691fd3a4b538e795440917f6907:shared/smf_arcade_rom_thumbnails'),
('arcadeRomIconRemoteQty','80'),
('arcadeRomUserSettings','webgl2Enabled|volume|mute|shader'),
('arcadeRuffleDefaultLink','https://usc1.contabostorage.com/2b82b691fd3a4b538e795440917f6907:shared/ruffle-web-selfhosted.zip'),
('arcadeRuffleExternalLatestDate','https://github.com/ruffle-rs/ruffle/releases'),
('arcadeRuffleExternalLink','https://ruffle.rs/downloads#website-package'),
('arcadeRuffleVersion','2010.01.01'),
('arcadeSecretIdVar','1111111111'),
('arcadeShowIC','1'),
('arcadeShowInfoCenter','1'),
('arcadeShowOnline','1'),
('arcadeSkin','1'),
('arcadeUploadSystem','1'),
('arcadeVersion','2.7.0.8'),
('arcade_alternateBGC','1'),
('arcade_cache_enable','1'),
('arcade_cache_shouttime','360'),
('arcade_cache_time','360'),
('arcade_ejs_threads','psp'),
('arcade_emulatorjs_fullscreen','1'),
('arcade_emulatorjs_gamepad','1'),
('arcade_emulatorjs_loadfiles','1'),
('arcade_emulatorjs_loadstate','1'),
('arcade_emulatorjs_savefiles','1'),
('arcade_emulatorjs_savestate','1'),
('arcade_emulatorjs_settings','1'),
('arcade_flash_emulator','1'),
('arcade_rom_emulator','1'),
('arcade_shoutboxC','1'),
('arcade_shoutboxC_name','Arcade Shouts'),
('arcade_shout_heightC','40'),
('arcade_shout_interval','10'),
('arcade_shout_intervalC','10'),
('arcade_shout_widthB','90'),
('arcade_show_shoutsC','20'),
('arcade_suggest_time','5'),
('arcade_thumbHeightGeneric','80'),
('arcade_thumbHeightRetro','70'),
('arcade_thumbHeightVintage','40'),
('arcade_thumbWidthGeneric','80'),
('arcade_thumbWidthRetro','70'),
('arcade_thumbWidthVintage','40'),
('defaultMaxMembers','30'),
('gamesDirectory','/srv/dumb/Games'),
('gamesPerPage','28'),
('gamesUrl','http://localhost:4000/Games'),
('game_of_day','1'),
('game_time','260906'),
('matchesPerPage','25'),
('romGamesDirectory','/srv/dumb/ArcadeRetroArch/roms'),
('romGamesUrl','http://localhost:4000/ArcadeRetroArch/roms'),
('scoresPerPage','50'),
('skin_avatar_sizeb_height','30'),
('skin_avatar_sizeb_width','30'),
('skin_avatar_size_height','30'),
('skin_avatar_size_heightA','50'),
('skin_avatar_size_width','30'),
('skin_avatar_size_widthA','50'),
('skin_best_playersB','5'),
('skin_latest_champs','5'),
('skin_latest_champsA','5'),
('skin_latest_champsB','5'),
('skin_latest_games','10'),
('skin_latest_gamesA','5'),
('skin_latest_gamesB','5'),
('skin_latest_rom_games','30'),
('skin_latest_scores','5'),
('skin_latest_scoresA','5'),
('skin_longest_champsB','5'),
('skin_most_popular','10'),
('skin_most_popularA','5'),
('skin_most_popularB','5'),
('skin_most_rom_popular','30'),
('smfVersion','2.1.7');
/*!40000 ALTER TABLE `smf_arcade_modsettings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_arcade_newshouts`
--

DROP TABLE IF EXISTS `smf_arcade_newshouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_newshouts` (
  `id_shout` int(11) NOT NULL AUTO_INCREMENT,
  `id_member` int(11) NOT NULL,
  `content` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id_shout`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_pdl1`
--

DROP TABLE IF EXISTS `smf_arcade_pdl1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_pdl1` (
  `id_member` int(10) unsigned NOT NULL,
  `count` int(10) unsigned DEFAULT NULL,
  `year` varchar(255) DEFAULT '',
  `day` varchar(255) DEFAULT '',
  `latest_year` varchar(255) DEFAULT '',
  `latest_day` varchar(255) DEFAULT '',
  `permission` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_pdl2`
--

DROP TABLE IF EXISTS `smf_arcade_pdl2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_pdl2` (
  `pdl_gameid` int(10) unsigned NOT NULL,
  `game_name` varchar(255) DEFAULT '',
  `report_day` varchar(255) DEFAULT '',
  `report_year` varchar(255) DEFAULT '',
  `user_id` int(10) unsigned DEFAULT NULL,
  `report_id` int(10) unsigned DEFAULT NULL,
  `report_reason` varchar(255) DEFAULT '',
  `download_count` int(10) unsigned DEFAULT NULL,
  `download_disable` int(10) unsigned DEFAULT NULL,
  `rom_arcade` int(10) unsigned DEFAULT 0,
  PRIMARY KEY (`pdl_gameid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_rates`
--

DROP TABLE IF EXISTS `smf_arcade_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_rates` (
  `id_member` int(10) unsigned NOT NULL,
  `id_game` int(10) unsigned NOT NULL,
  `rating` int(10) unsigned DEFAULT 0,
  `rate_time` int(10) unsigned DEFAULT 0,
  PRIMARY KEY (`id_game`,`id_member`),
  KEY `id_game` (`id_game`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_scores`
--

DROP TABLE IF EXISTS `smf_arcade_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_scores` (
  `id_score` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_game` int(10) unsigned DEFAULT NULL,
  `id_member` int(10) unsigned DEFAULT NULL,
  `score` float DEFAULT NULL,
  `duration` float DEFAULT NULL,
  `end_time` int(10) unsigned DEFAULT NULL,
  `champion_from` int(10) unsigned DEFAULT 0,
  `champion_to` int(10) unsigned DEFAULT 0,
  `position` int(10) unsigned DEFAULT 0,
  `personal_best` tinyint(3) unsigned DEFAULT 0,
  `score_status` varchar(255) DEFAULT '',
  `member_ip` varchar(255) DEFAULT '',
  `player_name` varchar(255) DEFAULT '',
  `comment` varchar(255) DEFAULT '',
  `validate_hash` varchar(255) DEFAULT '',
  PRIMARY KEY (`id_score`),
  KEY `id_game` (`id_game`),
  KEY `personal_best` (`id_member`,`personal_best`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_arcade_skins`
--

DROP TABLE IF EXISTS `smf_arcade_skins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_arcade_skins` (
  `id_skin` int(11) NOT NULL AUTO_INCREMENT,
  `skin_name` varchar(255) NOT NULL,
  `skin_source_file` varchar(255) NOT NULL,
  `skin_function` varchar(255) NOT NULL,
  `admin_function` varchar(255) NOT NULL,
  `lang_function` varchar(255) NOT NULL,
  `skin_template` varchar(255) NOT NULL,
  `enabled` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_skin`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_arcade_skins`
--

LOCK TABLES `smf_arcade_skins` WRITE;
/*!40000 ALTER TABLE `smf_arcade_skins` DISABLE KEYS */;
INSERT INTO `smf_arcade_skins` VALUES
(1,'Enterprise-C','Subs-ArcadeSkinC.php','','','','ArcadeSkinC',1),
(2,'Defiant','Subs-ArcadeSkinB.php','','','','ArcadeSkinB',1),
(3,'Classic','Subs-ArcadeSkinA.php','','','ArcadeEnterpriseLang','Arcade',1),
(4,'Enterprise-A','Subs-ArcadeSkinA.php','ArcadeSkinA','ArcadeEnterpriseAdmin','ArcadeEnterpriseLang','ArcadeSkinA',1);
/*!40000 ALTER TABLE `smf_arcade_skins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_attachments`
--

DROP TABLE IF EXISTS `smf_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_attachments` (
  `id_attach` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_thumb` int(10) unsigned NOT NULL DEFAULT 0,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_folder` tinyint(4) NOT NULL DEFAULT 1,
  `attachment_type` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `file_hash` varchar(40) NOT NULL DEFAULT '',
  `fileext` varchar(8) NOT NULL DEFAULT '',
  `size` int(10) unsigned NOT NULL DEFAULT 0,
  `downloads` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `width` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `height` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `mime_type` varchar(128) NOT NULL DEFAULT '',
  `approved` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_attach`),
  UNIQUE KEY `idx_id_member` (`id_member`,`id_attach`),
  KEY `idx_id_msg` (`id_msg`),
  KEY `idx_attachment_type` (`attachment_type`),
  KEY `idx_id_thumb` (`id_thumb`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_background_tasks`
--

DROP TABLE IF EXISTS `smf_background_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_background_tasks` (
  `id_task` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_file` varchar(255) NOT NULL DEFAULT '',
  `task_class` varchar(255) NOT NULL DEFAULT '',
  `task_data` mediumtext NOT NULL,
  `claimed_time` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_task`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_ban_groups`
--

DROP TABLE IF EXISTS `smf_ban_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_ban_groups` (
  `id_ban_group` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  `ban_time` int(10) unsigned NOT NULL DEFAULT 0,
  `expire_time` int(10) unsigned DEFAULT NULL,
  `cannot_access` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cannot_register` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cannot_post` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cannot_login` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `reason` varchar(255) NOT NULL DEFAULT '',
  `notes` text NOT NULL,
  PRIMARY KEY (`id_ban_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_ban_items`
--

DROP TABLE IF EXISTS `smf_ban_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_ban_items` (
  `id_ban` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id_ban_group` smallint(5) unsigned NOT NULL DEFAULT 0,
  `ip_low` varbinary(16) DEFAULT NULL,
  `ip_high` varbinary(16) DEFAULT NULL,
  `hostname` varchar(255) NOT NULL DEFAULT '',
  `email_address` varchar(255) NOT NULL DEFAULT '',
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `hits` mediumint(8) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_ban`),
  KEY `idx_id_ban_group` (`id_ban_group`),
  KEY `idx_id_ban_ip` (`ip_low`,`ip_high`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_board_permissions`
--

DROP TABLE IF EXISTS `smf_board_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_board_permissions` (
  `id_group` smallint(6) NOT NULL DEFAULT 0,
  `id_profile` smallint(5) unsigned NOT NULL DEFAULT 0,
  `permission` varchar(30) NOT NULL DEFAULT '',
  `add_deny` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_group`,`id_profile`,`permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_board_permissions`
--

LOCK TABLES `smf_board_permissions` WRITE;
/*!40000 ALTER TABLE `smf_board_permissions` DISABLE KEYS */;
INSERT INTO `smf_board_permissions` VALUES
(-1,1,'poll_view',1),
(-1,1,'view_attachments',1),
(-1,2,'poll_view',1),
(-1,3,'poll_view',1),
(-1,4,'poll_view',1),
(0,1,'delete_own',1),
(0,1,'lock_own',1),
(0,1,'modify_own',1),
(0,1,'poll_add_own',1),
(0,1,'poll_edit_own',1),
(0,1,'poll_lock_own',1),
(0,1,'poll_post',1),
(0,1,'poll_view',1),
(0,1,'poll_vote',1),
(0,1,'post_attachment',1),
(0,1,'post_draft',1),
(0,1,'post_new',1),
(0,1,'post_reply_any',1),
(0,1,'post_reply_own',1),
(0,1,'post_unapproved_attachments',1),
(0,1,'post_unapproved_replies_any',1),
(0,1,'post_unapproved_replies_own',1),
(0,1,'post_unapproved_topics',1),
(0,1,'remove_own',1),
(0,1,'report_any',1),
(0,1,'view_attachments',1),
(0,2,'delete_own',1),
(0,2,'lock_own',1),
(0,2,'modify_own',1),
(0,2,'poll_view',1),
(0,2,'poll_vote',1),
(0,2,'post_attachment',1),
(0,2,'post_draft',1),
(0,2,'post_new',1),
(0,2,'post_reply_any',1),
(0,2,'post_reply_own',1),
(0,2,'post_unapproved_attachments',1),
(0,2,'post_unapproved_replies_any',1),
(0,2,'post_unapproved_replies_own',1),
(0,2,'post_unapproved_topics',1),
(0,2,'remove_own',1),
(0,2,'report_any',1),
(0,2,'view_attachments',1),
(0,3,'delete_own',1),
(0,3,'lock_own',1),
(0,3,'modify_own',1),
(0,3,'poll_view',1),
(0,3,'poll_vote',1),
(0,3,'post_attachment',1),
(0,3,'post_reply_any',1),
(0,3,'post_reply_own',1),
(0,3,'post_unapproved_attachments',1),
(0,3,'post_unapproved_replies_any',1),
(0,3,'post_unapproved_replies_own',1),
(0,3,'remove_own',1),
(0,3,'report_any',1),
(0,3,'view_attachments',1),
(0,4,'poll_view',1),
(0,4,'poll_vote',1),
(0,4,'report_any',1),
(0,4,'view_attachments',1),
(2,1,'approve_posts',1),
(2,1,'delete_any',1),
(2,1,'delete_own',1),
(2,1,'lock_any',1),
(2,1,'lock_own',1),
(2,1,'make_sticky',1),
(2,1,'merge_any',1),
(2,1,'moderate_board',1),
(2,1,'modify_any',1),
(2,1,'modify_own',1),
(2,1,'move_any',1),
(2,1,'poll_add_any',1),
(2,1,'poll_edit_any',1),
(2,1,'poll_lock_any',1),
(2,1,'poll_post',1),
(2,1,'poll_remove_any',1),
(2,1,'poll_view',1),
(2,1,'poll_vote',1),
(2,1,'post_attachment',1),
(2,1,'post_draft',1),
(2,1,'post_new',1),
(2,1,'post_reply_any',1),
(2,1,'post_reply_own',1),
(2,1,'post_unapproved_attachments',1),
(2,1,'post_unapproved_replies_any',1),
(2,1,'post_unapproved_replies_own',1),
(2,1,'post_unapproved_topics',1),
(2,1,'remove_any',1),
(2,1,'report_any',1),
(2,1,'split_any',1),
(2,1,'view_attachments',1),
(2,2,'approve_posts',1),
(2,2,'delete_any',1),
(2,2,'delete_own',1),
(2,2,'lock_any',1),
(2,2,'lock_own',1),
(2,2,'make_sticky',1),
(2,2,'merge_any',1),
(2,2,'moderate_board',1),
(2,2,'modify_any',1),
(2,2,'modify_own',1),
(2,2,'move_any',1),
(2,2,'poll_add_any',1),
(2,2,'poll_edit_any',1),
(2,2,'poll_lock_any',1),
(2,2,'poll_post',1),
(2,2,'poll_remove_any',1),
(2,2,'poll_view',1),
(2,2,'poll_vote',1),
(2,2,'post_attachment',1),
(2,2,'post_draft',1),
(2,2,'post_new',1),
(2,2,'post_reply_any',1),
(2,2,'post_reply_own',1),
(2,2,'post_unapproved_attachments',1),
(2,2,'post_unapproved_replies_any',1),
(2,2,'post_unapproved_replies_own',1),
(2,2,'post_unapproved_topics',1),
(2,2,'remove_any',1),
(2,2,'report_any',1),
(2,2,'split_any',1),
(2,2,'view_attachments',1),
(2,3,'approve_posts',1),
(2,3,'delete_any',1),
(2,3,'delete_own',1),
(2,3,'lock_any',1),
(2,3,'lock_own',1),
(2,3,'make_sticky',1),
(2,3,'merge_any',1),
(2,3,'moderate_board',1),
(2,3,'modify_any',1),
(2,3,'modify_own',1),
(2,3,'move_any',1),
(2,3,'poll_add_any',1),
(2,3,'poll_edit_any',1),
(2,3,'poll_lock_any',1),
(2,3,'poll_post',1),
(2,3,'poll_remove_any',1),
(2,3,'poll_view',1),
(2,3,'poll_vote',1),
(2,3,'post_attachment',1),
(2,3,'post_draft',1),
(2,3,'post_new',1),
(2,3,'post_reply_any',1),
(2,3,'post_reply_own',1),
(2,3,'post_unapproved_attachments',1),
(2,3,'post_unapproved_replies_any',1),
(2,3,'post_unapproved_replies_own',1),
(2,3,'post_unapproved_topics',1),
(2,3,'remove_any',1),
(2,3,'report_any',1),
(2,3,'split_any',1),
(2,3,'view_attachments',1),
(2,4,'approve_posts',1),
(2,4,'delete_any',1),
(2,4,'delete_own',1),
(2,4,'lock_any',1),
(2,4,'lock_own',1),
(2,4,'make_sticky',1),
(2,4,'merge_any',1),
(2,4,'moderate_board',1),
(2,4,'modify_any',1),
(2,4,'modify_own',1),
(2,4,'move_any',1),
(2,4,'poll_add_any',1),
(2,4,'poll_edit_any',1),
(2,4,'poll_lock_any',1),
(2,4,'poll_post',1),
(2,4,'poll_remove_any',1),
(2,4,'poll_view',1),
(2,4,'poll_vote',1),
(2,4,'post_attachment',1),
(2,4,'post_draft',1),
(2,4,'post_new',1),
(2,4,'post_reply_any',1),
(2,4,'post_reply_own',1),
(2,4,'post_unapproved_attachments',1),
(2,4,'post_unapproved_replies_any',1),
(2,4,'post_unapproved_replies_own',1),
(2,4,'post_unapproved_topics',1),
(2,4,'remove_any',1),
(2,4,'report_any',1),
(2,4,'split_any',1),
(2,4,'view_attachments',1),
(3,1,'approve_posts',1),
(3,1,'delete_any',1),
(3,1,'delete_own',1),
(3,1,'lock_any',1),
(3,1,'lock_own',1),
(3,1,'make_sticky',1),
(3,1,'merge_any',1),
(3,1,'moderate_board',1),
(3,1,'modify_any',1),
(3,1,'modify_own',1),
(3,1,'move_any',1),
(3,1,'poll_add_any',1),
(3,1,'poll_edit_any',1),
(3,1,'poll_lock_any',1),
(3,1,'poll_post',1),
(3,1,'poll_remove_any',1),
(3,1,'poll_view',1),
(3,1,'poll_vote',1),
(3,1,'post_attachment',1),
(3,1,'post_draft',1),
(3,1,'post_new',1),
(3,1,'post_reply_any',1),
(3,1,'post_reply_own',1),
(3,1,'post_unapproved_attachments',1),
(3,1,'post_unapproved_replies_any',1),
(3,1,'post_unapproved_replies_own',1),
(3,1,'post_unapproved_topics',1),
(3,1,'remove_any',1),
(3,1,'report_any',1),
(3,1,'split_any',1),
(3,1,'view_attachments',1),
(3,2,'approve_posts',1),
(3,2,'delete_any',1),
(3,2,'delete_own',1),
(3,2,'lock_any',1),
(3,2,'lock_own',1),
(3,2,'make_sticky',1),
(3,2,'merge_any',1),
(3,2,'moderate_board',1),
(3,2,'modify_any',1),
(3,2,'modify_own',1),
(3,2,'move_any',1),
(3,2,'poll_add_any',1),
(3,2,'poll_edit_any',1),
(3,2,'poll_lock_any',1),
(3,2,'poll_post',1),
(3,2,'poll_remove_any',1),
(3,2,'poll_view',1),
(3,2,'poll_vote',1),
(3,2,'post_attachment',1),
(3,2,'post_draft',1),
(3,2,'post_new',1),
(3,2,'post_reply_any',1),
(3,2,'post_reply_own',1),
(3,2,'post_unapproved_attachments',1),
(3,2,'post_unapproved_replies_any',1),
(3,2,'post_unapproved_replies_own',1),
(3,2,'post_unapproved_topics',1),
(3,2,'remove_any',1),
(3,2,'report_any',1),
(3,2,'split_any',1),
(3,2,'view_attachments',1),
(3,3,'approve_posts',1),
(3,3,'delete_any',1),
(3,3,'delete_own',1),
(3,3,'lock_any',1),
(3,3,'lock_own',1),
(3,3,'make_sticky',1),
(3,3,'merge_any',1),
(3,3,'moderate_board',1),
(3,3,'modify_any',1),
(3,3,'modify_own',1),
(3,3,'move_any',1),
(3,3,'poll_add_any',1),
(3,3,'poll_edit_any',1),
(3,3,'poll_lock_any',1),
(3,3,'poll_post',1),
(3,3,'poll_remove_any',1),
(3,3,'poll_view',1),
(3,3,'poll_vote',1),
(3,3,'post_attachment',1),
(3,3,'post_draft',1),
(3,3,'post_new',1),
(3,3,'post_reply_any',1),
(3,3,'post_reply_own',1),
(3,3,'post_unapproved_attachments',1),
(3,3,'post_unapproved_replies_any',1),
(3,3,'post_unapproved_replies_own',1),
(3,3,'post_unapproved_topics',1),
(3,3,'remove_any',1),
(3,3,'report_any',1),
(3,3,'split_any',1),
(3,3,'view_attachments',1),
(3,4,'approve_posts',1),
(3,4,'delete_any',1),
(3,4,'delete_own',1),
(3,4,'lock_any',1),
(3,4,'lock_own',1),
(3,4,'make_sticky',1),
(3,4,'merge_any',1),
(3,4,'moderate_board',1),
(3,4,'modify_any',1),
(3,4,'modify_own',1),
(3,4,'move_any',1),
(3,4,'poll_add_any',1),
(3,4,'poll_edit_any',1),
(3,4,'poll_lock_any',1),
(3,4,'poll_post',1),
(3,4,'poll_remove_any',1),
(3,4,'poll_view',1),
(3,4,'poll_vote',1),
(3,4,'post_attachment',1),
(3,4,'post_draft',1),
(3,4,'post_new',1),
(3,4,'post_reply_any',1),
(3,4,'post_reply_own',1),
(3,4,'post_unapproved_attachments',1),
(3,4,'post_unapproved_replies_any',1),
(3,4,'post_unapproved_replies_own',1),
(3,4,'post_unapproved_topics',1),
(3,4,'remove_any',1),
(3,4,'report_any',1),
(3,4,'split_any',1),
(3,4,'view_attachments',1),
(10,1,'delete_own',1),
(10,1,'lock_own',1),
(10,1,'modify_own',1),
(10,1,'poll_add_own',1),
(10,1,'poll_edit_own',1),
(10,1,'poll_lock_own',1),
(10,1,'poll_post',1),
(10,1,'poll_view',1),
(10,1,'poll_vote',1),
(10,1,'post_attachment',1),
(10,1,'post_draft',1),
(10,1,'post_new',1),
(10,1,'post_reply_any',1),
(10,1,'post_reply_own',1),
(10,1,'post_unapproved_attachments',1),
(10,1,'post_unapproved_replies_any',1),
(10,1,'post_unapproved_replies_own',1),
(10,1,'post_unapproved_topics',1),
(10,1,'remove_own',1),
(10,1,'report_any',1),
(10,1,'view_attachments',1),
(10,2,'delete_own',1),
(10,2,'lock_own',1),
(10,2,'modify_own',1),
(10,2,'poll_view',1),
(10,2,'poll_vote',1),
(10,2,'post_attachment',1),
(10,2,'post_draft',1),
(10,2,'post_new',1),
(10,2,'post_reply_any',1),
(10,2,'post_reply_own',1),
(10,2,'post_unapproved_attachments',1),
(10,2,'post_unapproved_replies_any',1),
(10,2,'post_unapproved_replies_own',1),
(10,2,'post_unapproved_topics',1),
(10,2,'remove_own',1),
(10,2,'report_any',1),
(10,2,'view_attachments',1),
(10,3,'delete_own',1),
(10,3,'lock_own',1),
(10,3,'modify_own',1),
(10,3,'poll_view',1),
(10,3,'poll_vote',1),
(10,3,'post_attachment',1),
(10,3,'post_reply_any',1),
(10,3,'post_reply_own',1),
(10,3,'post_unapproved_attachments',1),
(10,3,'post_unapproved_replies_any',1),
(10,3,'post_unapproved_replies_own',1),
(10,3,'remove_own',1),
(10,3,'report_any',1),
(10,3,'view_attachments',1),
(10,4,'poll_view',1),
(10,4,'poll_vote',1),
(10,4,'report_any',1),
(10,4,'view_attachments',1);
/*!40000 ALTER TABLE `smf_board_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_board_permissions_view`
--

DROP TABLE IF EXISTS `smf_board_permissions_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_board_permissions_view` (
  `id_group` smallint(6) NOT NULL DEFAULT 0,
  `id_board` smallint(5) unsigned NOT NULL,
  `deny` smallint(6) NOT NULL,
  PRIMARY KEY (`id_group`,`id_board`,`deny`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_board_permissions_view`
--

LOCK TABLES `smf_board_permissions_view` WRITE;
/*!40000 ALTER TABLE `smf_board_permissions_view` DISABLE KEYS */;
INSERT INTO `smf_board_permissions_view` VALUES
(-1,1,0),
(0,1,0),
(2,1,0),
(9,1,0),
(10,1,0);
/*!40000 ALTER TABLE `smf_board_permissions_view` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_boards`
--

DROP TABLE IF EXISTS `smf_boards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_boards` (
  `id_board` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `id_cat` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `child_level` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `id_parent` smallint(5) unsigned NOT NULL DEFAULT 0,
  `board_order` smallint(6) NOT NULL DEFAULT 0,
  `id_last_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `id_msg_updated` int(10) unsigned NOT NULL DEFAULT 0,
  `member_groups` varchar(255) NOT NULL DEFAULT '-1,0',
  `id_profile` smallint(5) unsigned NOT NULL DEFAULT 1,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `num_topics` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `num_posts` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `count_posts` tinyint(4) NOT NULL DEFAULT 0,
  `id_theme` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `override_theme` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `unapproved_posts` smallint(6) NOT NULL DEFAULT 0,
  `unapproved_topics` smallint(6) NOT NULL DEFAULT 0,
  `redirect` varchar(255) NOT NULL DEFAULT '',
  `deny_member_groups` varchar(255) NOT NULL DEFAULT '',
  `Shop_credits_count` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `Shop_credits_topic` int(10) unsigned NOT NULL DEFAULT 0,
  `Shop_credits_post` int(10) unsigned NOT NULL DEFAULT 0,
  `Shop_credits_bonus` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_board`),
  UNIQUE KEY `idx_categories` (`id_cat`,`id_board`),
  KEY `idx_id_parent` (`id_parent`),
  KEY `idx_id_msg_updated` (`id_msg_updated`),
  KEY `idx_member_groups` (`member_groups`(48))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_boards`
--

LOCK TABLES `smf_boards` WRITE;
/*!40000 ALTER TABLE `smf_boards` DISABLE KEYS */;
INSERT INTO `smf_boards` VALUES
(1,1,0,0,1,10,10,'-1,0,2,10',1,'General Discussion','Feel free to talk about anything and everything in this board.',0,0,0,0,0,0,0,'','',1,0,0,0);
/*!40000 ALTER TABLE `smf_boards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_calendar`
--

DROP TABLE IF EXISTS `smf_calendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_calendar` (
  `id_event` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `start_date` date NOT NULL DEFAULT '1004-01-01',
  `end_date` date NOT NULL DEFAULT '1004-01-01',
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `timezone` varchar(80) DEFAULT NULL,
  `location` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_event`),
  KEY `idx_start_date` (`start_date`),
  KEY `idx_end_date` (`end_date`),
  KEY `idx_topic` (`id_topic`,`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_calendar_holidays`
--

DROP TABLE IF EXISTS `smf_calendar_holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_calendar_holidays` (
  `id_holiday` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `event_date` date NOT NULL DEFAULT '1004-01-01',
  `title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_holiday`),
  KEY `idx_event_date` (`event_date`)
) ENGINE=InnoDB AUTO_INCREMENT=204 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_categories`
--

DROP TABLE IF EXISTS `smf_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_categories` (
  `id_cat` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `cat_order` tinyint(4) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `can_collapse` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_cat`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_categories`
--

LOCK TABLES `smf_categories` WRITE;
/*!40000 ALTER TABLE `smf_categories` DISABLE KEYS */;
INSERT INTO `smf_categories` VALUES
(1,0,'General Category','',1);
/*!40000 ALTER TABLE `smf_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_custom_fields`
--

DROP TABLE IF EXISTS `smf_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_custom_fields` (
  `id_field` smallint(6) NOT NULL AUTO_INCREMENT,
  `col_name` varchar(12) NOT NULL DEFAULT '',
  `field_name` varchar(40) NOT NULL DEFAULT '',
  `field_desc` varchar(255) NOT NULL DEFAULT '',
  `field_type` varchar(8) NOT NULL DEFAULT 'text',
  `field_length` smallint(6) NOT NULL DEFAULT 255,
  `field_options` text NOT NULL,
  `field_order` smallint(6) NOT NULL DEFAULT 0,
  `mask` varchar(255) NOT NULL DEFAULT '',
  `show_reg` tinyint(4) NOT NULL DEFAULT 0,
  `show_display` tinyint(4) NOT NULL DEFAULT 0,
  `show_mlist` smallint(6) NOT NULL DEFAULT 0,
  `show_profile` varchar(20) NOT NULL DEFAULT 'forumprofile',
  `private` tinyint(4) NOT NULL DEFAULT 0,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `bbc` tinyint(4) NOT NULL DEFAULT 0,
  `can_search` tinyint(4) NOT NULL DEFAULT 0,
  `default_value` varchar(255) NOT NULL DEFAULT '',
  `enclose` text NOT NULL,
  `placement` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_field`),
  UNIQUE KEY `idx_col_name` (`col_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_custom_fields`
--

LOCK TABLES `smf_custom_fields` WRITE;
/*!40000 ALTER TABLE `smf_custom_fields` DISABLE KEYS */;
INSERT INTO `smf_custom_fields` VALUES
(3,'cust_loca','{location}','{location_desc}','text',50,'',3,'nohtml',0,1,0,'forumprofile',0,1,0,0,'','',0),
(5,'cust_descri','Description','Enter your profile description here!','textarea',3000,'',5,'nohtml',0,0,0,'forumprofile',0,1,1,0,'30,10','',2),
(6,'cust_testfi','Test Field','','text',255,'',6,'nohtml',0,0,0,'forumprofile',0,1,0,0,'','',0);
/*!40000 ALTER TABLE `smf_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_group_moderators`
--

DROP TABLE IF EXISTS `smf_group_moderators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_group_moderators` (
  `id_group` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_group`,`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_actions`
--

DROP TABLE IF EXISTS `smf_log_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_actions` (
  `id_action` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_log` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `log_time` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ip` varbinary(16) DEFAULT NULL,
  `action` varchar(30) NOT NULL DEFAULT '',
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `extra` text NOT NULL,
  PRIMARY KEY (`id_action`),
  KEY `idx_id_log` (`id_log`),
  KEY `idx_log_time` (`log_time`),
  KEY `idx_id_member` (`id_member`),
  KEY `idx_id_board` (`id_board`),
  KEY `idx_id_msg` (`id_msg`),
  KEY `idx_id_topic_id_log` (`id_topic`,`id_log`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_activity`
--

DROP TABLE IF EXISTS `smf_log_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_activity` (
  `date` date NOT NULL,
  `hits` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `topics` smallint(5) unsigned NOT NULL DEFAULT 0,
  `posts` smallint(5) unsigned NOT NULL DEFAULT 0,
  `registers` smallint(5) unsigned NOT NULL DEFAULT 0,
  `most_on` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_banned`
--

DROP TABLE IF EXISTS `smf_log_banned`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_banned` (
  `id_ban_log` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ip` varbinary(16) DEFAULT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `log_time` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_ban_log`),
  KEY `idx_log_time` (`log_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_boards`
--

DROP TABLE IF EXISTS `smf_log_boards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_boards` (
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_member`,`id_board`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_comments`
--

DROP TABLE IF EXISTS `smf_log_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_comments` (
  `id_comment` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `member_name` varchar(80) NOT NULL DEFAULT '',
  `comment_type` varchar(8) NOT NULL DEFAULT 'warning',
  `id_recipient` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `recipient_name` varchar(255) NOT NULL DEFAULT '',
  `log_time` int(11) NOT NULL DEFAULT 0,
  `id_notice` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `counter` tinyint(4) NOT NULL DEFAULT 0,
  `body` text NOT NULL,
  PRIMARY KEY (`id_comment`),
  KEY `idx_id_recipient` (`id_recipient`),
  KEY `idx_log_time` (`log_time`),
  KEY `idx_comment_type` (`comment_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_digest`
--

DROP TABLE IF EXISTS `smf_log_digest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_digest` (
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `note_type` varchar(10) NOT NULL DEFAULT 'post',
  `daily` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `exclude` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_errors`
--

DROP TABLE IF EXISTS `smf_log_errors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_errors` (
  `id_error` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `log_time` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ip` varbinary(16) DEFAULT NULL,
  `url` text NOT NULL,
  `message` text NOT NULL,
  `session` varchar(128) NOT NULL DEFAULT '',
  `error_type` char(15) NOT NULL DEFAULT 'general',
  `file` varchar(255) NOT NULL DEFAULT '',
  `line` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `backtrace` varchar(10000) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_error`),
  KEY `idx_log_time` (`log_time`),
  KEY `idx_id_member` (`id_member`),
  KEY `idx_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_floodcontrol`
--

DROP TABLE IF EXISTS `smf_log_floodcontrol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_floodcontrol` (
  `ip` varbinary(16) NOT NULL,
  `log_time` int(10) unsigned NOT NULL DEFAULT 0,
  `log_type` varchar(30) NOT NULL DEFAULT 'post',
  PRIMARY KEY (`ip`,`log_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_group_requests`
--

DROP TABLE IF EXISTS `smf_log_group_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_group_requests` (
  `id_request` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_group` smallint(5) unsigned NOT NULL DEFAULT 0,
  `time_applied` int(10) unsigned NOT NULL DEFAULT 0,
  `reason` text NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `id_member_acted` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `member_name_acted` varchar(255) NOT NULL DEFAULT '',
  `time_acted` int(10) unsigned NOT NULL DEFAULT 0,
  `act_reason` text NOT NULL,
  PRIMARY KEY (`id_request`),
  KEY `idx_id_member` (`id_member`,`id_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_mark_read`
--

DROP TABLE IF EXISTS `smf_log_mark_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_mark_read` (
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_member`,`id_board`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_member_notices`
--

DROP TABLE IF EXISTS `smf_log_member_notices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_member_notices` (
  `id_notice` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  PRIMARY KEY (`id_notice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_notify`
--

DROP TABLE IF EXISTS `smf_log_notify`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_notify` (
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `sent` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_member`,`id_topic`,`id_board`),
  KEY `idx_id_topic` (`id_topic`,`id_member`),
  KEY `id_board` (`id_board`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_online`
--

DROP TABLE IF EXISTS `smf_log_online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_online` (
  `session` varchar(128) NOT NULL DEFAULT '',
  `log_time` int(11) NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_spider` smallint(5) unsigned NOT NULL DEFAULT 0,
  `ip` varbinary(16) DEFAULT NULL,
  `url` varchar(2048) NOT NULL DEFAULT '',
  PRIMARY KEY (`session`),
  KEY `idx_log_time` (`log_time`),
  KEY `idx_id_member` (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_packages`
--

DROP TABLE IF EXISTS `smf_log_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_packages` (
  `id_install` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `package_id` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `version` varchar(255) NOT NULL DEFAULT '',
  `id_member_installed` mediumint(9) NOT NULL DEFAULT 0,
  `member_installed` varchar(255) NOT NULL DEFAULT '',
  `time_installed` int(11) NOT NULL DEFAULT 0,
  `id_member_removed` mediumint(9) NOT NULL DEFAULT 0,
  `member_removed` varchar(255) NOT NULL DEFAULT '',
  `time_removed` int(11) NOT NULL DEFAULT 0,
  `install_state` tinyint(4) NOT NULL DEFAULT 1,
  `failed_steps` text NOT NULL,
  `themes_installed` varchar(255) NOT NULL DEFAULT '',
  `db_changes` text NOT NULL,
  `credits` text NOT NULL,
  `sha256_hash` text DEFAULT NULL,
  PRIMARY KEY (`id_install`),
  KEY `idx_filename` (`filename`(15))
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_polls`
--

DROP TABLE IF EXISTS `smf_log_polls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_polls` (
  `id_poll` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_choice` tinyint(3) unsigned NOT NULL DEFAULT 0,
  KEY `idx_id_poll` (`id_poll`,`id_member`,`id_choice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_reported`
--

DROP TABLE IF EXISTS `smf_log_reported`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_reported` (
  `id_report` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `membername` varchar(255) NOT NULL DEFAULT '',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `body` mediumtext NOT NULL,
  `time_started` int(11) NOT NULL DEFAULT 0,
  `time_updated` int(11) NOT NULL DEFAULT 0,
  `num_reports` mediumint(9) NOT NULL DEFAULT 0,
  `closed` tinyint(4) NOT NULL DEFAULT 0,
  `ignore_all` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_report`),
  KEY `idx_id_member` (`id_member`),
  KEY `idx_id_topic` (`id_topic`),
  KEY `idx_closed` (`closed`),
  KEY `idx_time_started` (`time_started`),
  KEY `idx_id_msg` (`id_msg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_reported_comments`
--

DROP TABLE IF EXISTS `smf_log_reported_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_reported_comments` (
  `id_comment` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id_report` mediumint(9) NOT NULL DEFAULT 0,
  `id_member` mediumint(9) NOT NULL,
  `membername` varchar(255) NOT NULL DEFAULT '',
  `member_ip` varbinary(16) DEFAULT NULL,
  `comment` varchar(255) NOT NULL DEFAULT '',
  `time_sent` int(11) NOT NULL,
  PRIMARY KEY (`id_comment`),
  KEY `idx_id_report` (`id_report`),
  KEY `idx_id_member` (`id_member`),
  KEY `idx_time_sent` (`time_sent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_scheduled_tasks`
--

DROP TABLE IF EXISTS `smf_log_scheduled_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_scheduled_tasks` (
  `id_log` mediumint(9) NOT NULL AUTO_INCREMENT,
  `id_task` smallint(6) NOT NULL DEFAULT 0,
  `time_run` int(11) NOT NULL DEFAULT 0,
  `time_taken` float NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_search_messages`
--

DROP TABLE IF EXISTS `smf_log_search_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_search_messages` (
  `id_search` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_search`,`id_msg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_search_results`
--

DROP TABLE IF EXISTS `smf_log_search_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_search_results` (
  `id_search` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `relevance` smallint(5) unsigned NOT NULL DEFAULT 0,
  `num_matches` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_search`,`id_topic`,`id_msg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_search_subjects`
--

DROP TABLE IF EXISTS `smf_log_search_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_search_subjects` (
  `word` varchar(20) NOT NULL DEFAULT '',
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`word`,`id_topic`),
  KEY `idx_id_topic` (`id_topic`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_search_topics`
--

DROP TABLE IF EXISTS `smf_log_search_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_search_topics` (
  `id_search` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_search`,`id_topic`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_spider_hits`
--

DROP TABLE IF EXISTS `smf_log_spider_hits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_spider_hits` (
  `id_hit` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_spider` smallint(5) unsigned NOT NULL DEFAULT 0,
  `log_time` int(10) unsigned NOT NULL DEFAULT 0,
  `url` varchar(1024) NOT NULL DEFAULT '',
  `processed` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_hit`),
  KEY `idx_id_spider` (`id_spider`),
  KEY `idx_log_time` (`log_time`),
  KEY `idx_processed` (`processed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_spider_stats`
--

DROP TABLE IF EXISTS `smf_log_spider_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_spider_stats` (
  `id_spider` smallint(5) unsigned NOT NULL DEFAULT 0,
  `page_hits` int(11) NOT NULL DEFAULT 0,
  `last_seen` int(10) unsigned NOT NULL DEFAULT 0,
  `stat_date` date NOT NULL DEFAULT '1004-01-01',
  PRIMARY KEY (`stat_date`,`id_spider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_subscribed`
--

DROP TABLE IF EXISTS `smf_log_subscribed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_subscribed` (
  `id_sublog` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_subscribe` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_member` int(11) NOT NULL DEFAULT 0,
  `old_id_group` smallint(6) NOT NULL DEFAULT 0,
  `start_time` int(11) NOT NULL DEFAULT 0,
  `end_time` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `payments_pending` tinyint(4) NOT NULL DEFAULT 0,
  `pending_details` text NOT NULL,
  `reminder_sent` tinyint(4) NOT NULL DEFAULT 0,
  `vendor_ref` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_sublog`),
  UNIQUE KEY `id_subscribe` (`id_subscribe`,`id_member`),
  KEY `idx_end_time` (`end_time`),
  KEY `idx_reminder_sent` (`reminder_sent`),
  KEY `idx_payments_pending` (`payments_pending`),
  KEY `idx_status` (`status`),
  KEY `idx_id_member` (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_log_topics`
--

DROP TABLE IF EXISTS `smf_log_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_log_topics` (
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `unwatched` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_member`,`id_topic`),
  KEY `idx_id_topic` (`id_topic`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_mail_queue`
--

DROP TABLE IF EXISTS `smf_mail_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_mail_queue` (
  `id_mail` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time_sent` int(11) NOT NULL DEFAULT 0,
  `recipient` varchar(255) NOT NULL DEFAULT '',
  `body` mediumtext NOT NULL,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `headers` text NOT NULL,
  `send_html` tinyint(4) NOT NULL DEFAULT 0,
  `priority` tinyint(4) NOT NULL DEFAULT 1,
  `private` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_mail`),
  KEY `idx_time_sent` (`time_sent`),
  KEY `idx_mail_priority` (`priority`,`id_mail`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_member_logins`
--

DROP TABLE IF EXISTS `smf_member_logins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_member_logins` (
  `id_login` int(11) NOT NULL AUTO_INCREMENT,
  `id_member` mediumint(9) NOT NULL DEFAULT 0,
  `time` int(11) NOT NULL DEFAULT 0,
  `ip` varbinary(16) DEFAULT NULL,
  `ip2` varbinary(16) DEFAULT NULL,
  PRIMARY KEY (`id_login`),
  KEY `idx_id_member` (`id_member`),
  KEY `idx_time` (`time`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_membergroups`
--

DROP TABLE IF EXISTS `smf_membergroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_membergroups` (
  `id_group` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(80) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `online_color` varchar(20) NOT NULL DEFAULT '',
  `min_posts` mediumint(9) NOT NULL DEFAULT -1,
  `max_messages` smallint(5) unsigned NOT NULL DEFAULT 0,
  `icons` varchar(255) NOT NULL DEFAULT '',
  `group_type` tinyint(4) NOT NULL DEFAULT 0,
  `hidden` tinyint(4) NOT NULL DEFAULT 0,
  `id_parent` smallint(6) NOT NULL DEFAULT -2,
  `tfa_required` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_group`),
  KEY `idx_min_posts` (`min_posts`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_membergroups`
--

LOCK TABLES `smf_membergroups` WRITE;
/*!40000 ALTER TABLE `smf_membergroups` DISABLE KEYS */;
INSERT INTO `smf_membergroups` VALUES
(1,'Administrator','','#FF0000',-1,0,'5#iconadmin.png',1,0,-2,0),
(2,'Global Moderator','','#0000FF',-1,0,'5#icongmod.png',0,0,-2,0),
(3,'Moderator','','',-1,0,'5#iconmod.png',0,0,-2,0),
(4,'Newbie','','',0,0,'1#icon.png',0,0,-2,0),
(5,'Jr. Member','','',50,0,'2#icon.png',0,0,-2,0),
(6,'Full Member','','',100,0,'3#icon.png',0,0,-2,0),
(7,'Sr. Member','','',250,0,'4#icon.png',0,0,-2,0),
(8,'Hero Member','','',500,0,'5#icon.png',0,0,-2,0),
(10,'Test','','',-1,0,'1#icon.png',0,0,-2,0);
/*!40000 ALTER TABLE `smf_membergroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_members`
--

DROP TABLE IF EXISTS `smf_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_members` (
  `id_member` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `member_name` varchar(80) NOT NULL DEFAULT '',
  `date_registered` int(10) unsigned NOT NULL DEFAULT 0,
  `posts` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_group` smallint(5) unsigned NOT NULL DEFAULT 0,
  `lngfile` varchar(255) NOT NULL DEFAULT '',
  `last_login` int(10) unsigned NOT NULL DEFAULT 0,
  `real_name` varchar(255) NOT NULL DEFAULT '',
  `instant_messages` smallint(6) NOT NULL DEFAULT 0,
  `unread_messages` smallint(6) NOT NULL DEFAULT 0,
  `new_pm` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `alerts` int(10) unsigned NOT NULL DEFAULT 0,
  `buddy_list` text NOT NULL,
  `pm_ignore_list` text DEFAULT NULL,
  `pm_prefs` mediumint(9) NOT NULL DEFAULT 0,
  `mod_prefs` varchar(20) NOT NULL DEFAULT '',
  `passwd` varchar(64) NOT NULL DEFAULT '',
  `email_address` varchar(255) NOT NULL DEFAULT '',
  `personal_text` varchar(255) NOT NULL DEFAULT '',
  `birthdate` date NOT NULL DEFAULT '1004-01-01',
  `website_title` varchar(255) NOT NULL DEFAULT '',
  `website_url` varchar(255) NOT NULL DEFAULT '',
  `show_online` tinyint(4) NOT NULL DEFAULT 1,
  `time_format` varchar(80) NOT NULL DEFAULT '',
  `signature` text NOT NULL,
  `time_offset` float NOT NULL DEFAULT 0,
  `avatar` varchar(255) NOT NULL DEFAULT '',
  `usertitle` varchar(255) NOT NULL DEFAULT '',
  `member_ip` varbinary(16) DEFAULT NULL,
  `member_ip2` varbinary(16) DEFAULT NULL,
  `secret_question` varchar(255) NOT NULL DEFAULT '',
  `secret_answer` varchar(64) NOT NULL DEFAULT '',
  `id_theme` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `is_activated` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `validation_code` varchar(10) NOT NULL DEFAULT '',
  `id_msg_last_visit` int(10) unsigned NOT NULL DEFAULT 0,
  `additional_groups` varchar(255) NOT NULL DEFAULT '',
  `smiley_set` varchar(48) NOT NULL DEFAULT '',
  `id_post_group` smallint(5) unsigned NOT NULL DEFAULT 0,
  `total_time_logged_in` int(10) unsigned NOT NULL DEFAULT 0,
  `password_salt` varchar(255) NOT NULL DEFAULT '',
  `ignore_boards` text NOT NULL,
  `warning` tinyint(4) NOT NULL DEFAULT 0,
  `passwd_flood` varchar(12) NOT NULL DEFAULT '',
  `pm_receive_from` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `timezone` varchar(80) NOT NULL DEFAULT '',
  `tfa_secret` varchar(24) NOT NULL DEFAULT '',
  `tfa_backup` varchar(64) NOT NULL DEFAULT '',
  `shopMoney` mediumint(9) NOT NULL DEFAULT 0,
  `shopBank` bigint(20) NOT NULL DEFAULT 0,
  `shopInventory_hide` int(10) unsigned NOT NULL DEFAULT 0,
  `gamesPass` int(10) unsigned NOT NULL DEFAULT 0,
  `mood_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `mood_color` varchar(7) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_member`),
  KEY `idx_member_name` (`member_name`),
  KEY `idx_real_name` (`real_name`),
  KEY `idx_email_address` (`email_address`),
  KEY `idx_date_registered` (`date_registered`),
  KEY `idx_id_group` (`id_group`),
  KEY `idx_birthdate` (`birthdate`),
  KEY `idx_posts` (`posts`),
  KEY `idx_last_login` (`last_login`),
  KEY `idx_lngfile` (`lngfile`(30)),
  KEY `idx_id_post_group` (`id_post_group`),
  KEY `idx_warning` (`warning`),
  KEY `idx_total_time_logged_in` (`total_time_logged_in`),
  KEY `idx_id_theme` (`id_theme`),
  KEY `idx_active_real_name` (`is_activated`,`real_name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_members`
--

LOCK TABLES `smf_members` WRITE;
/*!40000 ALTER TABLE `smf_members` DISABLE KEYS */;
INSERT INTO `smf_members` VALUES
(
  -- id_member
  1,
  -- member_name
  'test2',
  -- date_registered
  0,
  -- posts
  0,
  -- id_group
  1,
  -- lngfile
  '',
  -- last_login
  0,
  -- real_name
  'admin',
  -- instant_messages
  0,
  -- unread_messages
  0,
  -- new_pm
  0,
  -- alerts
  0,
  -- buddy_list
  '',
  -- pm_ignore_list
  '',
  -- pm_prefs
  0,
  -- mod_prefs
  '',
  -- passwd
  '$2y$13$duAE02bejHG4fkV7Hf/P9OcgulkhInymNvJrWPeVu29Y.08PQYc3i',
  -- email_address
  'test@example.com',
  -- personal_text
  '',
  -- birthdate
  '1004-01-01',
  -- website_title
  '',
  -- website_url
  '',
  -- show_online
  1,
  -- time_format
  '',
  -- signature
  '',
  -- time_offset
  0,
  -- avatar
  '',
  -- usertitle
  '',
  -- member_ip
  'aaaa',
  -- member_ip2
  'aaaa',
  -- secret_question
  '',
  -- secret_answer
  '',
  -- id_theme
  0,
  -- is_activated
  1,
  -- validation_code
  '',
  -- id_msg_last_visit
  5,
  -- additional_groups
  '',
  -- smiley_set
  '',
  -- id_post_group
  4,
  -- total_time_logged_in
  0,
  -- password_salt
  '51d053c6f8e729c241ac337ecde8f55a',
  -- ignore_boards
  '',
  -- warning
  0,
  -- passwd_flood
  '',
  -- pm_receive_from
  1,
  -- timezone
  'UTC',
  -- tfa_secret
  '',
  -- tfa_backup
  '',
  -- shopMoney
  50,
  -- shopBank
  0,
  -- shopInventory_hide
  0,
  -- gamesPass
  0,
  -- mood_id
  0,
  -- mood_color
  ''
);
/*!40000 ALTER TABLE `smf_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_mentions`
--

DROP TABLE IF EXISTS `smf_mentions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_mentions` (
  `content_id` int(11) NOT NULL DEFAULT 0,
  `content_type` varchar(10) NOT NULL DEFAULT '',
  `id_mentioned` int(11) NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `time` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`content_id`,`content_type`,`id_mentioned`),
  KEY `content` (`content_id`,`content_type`),
  KEY `mentionee` (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_message_icons`
--

DROP TABLE IF EXISTS `smf_message_icons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_message_icons` (
  `id_icon` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(80) NOT NULL DEFAULT '',
  `filename` varchar(80) NOT NULL DEFAULT '',
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `icon_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_icon`),
  KEY `idx_id_board` (`id_board`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_message_icons`
--

LOCK TABLES `smf_message_icons` WRITE;
/*!40000 ALTER TABLE `smf_message_icons` DISABLE KEYS */;
INSERT INTO `smf_message_icons` VALUES
(1,'Standard','xx',0,0),
(2,'Thumb Up','thumbup',0,1),
(3,'Thumb Down','thumbdown',0,2),
(4,'Exclamation point','exclamation',0,3),
(5,'Question mark','question',0,4),
(6,'Lamp','lamp',0,5),
(7,'Smiley','smiley',0,6),
(8,'Angry','angry',0,7),
(9,'Cheesy','cheesy',0,8),
(10,'Grin','grin',0,9),
(11,'Sad','sad',0,10),
(12,'Wink','wink',0,11),
(13,'Poll','poll',0,12);
/*!40000 ALTER TABLE `smf_message_icons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_messages`
--

DROP TABLE IF EXISTS `smf_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_messages` (
  `id_msg` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `poster_time` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_msg_modified` int(10) unsigned NOT NULL DEFAULT 0,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `poster_name` varchar(255) NOT NULL DEFAULT '',
  `poster_email` varchar(255) NOT NULL DEFAULT '',
  `poster_ip` varbinary(16) DEFAULT NULL,
  `smileys_enabled` tinyint(4) NOT NULL DEFAULT 1,
  `modified_time` int(10) unsigned NOT NULL DEFAULT 0,
  `modified_name` varchar(255) NOT NULL DEFAULT '',
  `modified_reason` varchar(255) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `icon` varchar(16) NOT NULL DEFAULT 'xx',
  `approved` tinyint(4) NOT NULL DEFAULT 1,
  `likes` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_msg`),
  UNIQUE KEY `idx_id_board` (`id_board`,`id_msg`,`approved`),
  UNIQUE KEY `idx_id_member` (`id_member`,`id_msg`),
  KEY `idx_ip_index` (`poster_ip`,`id_topic`),
  KEY `idx_participation` (`id_member`,`id_topic`),
  KEY `idx_show_posts` (`id_member`,`id_board`),
  KEY `idx_id_member_msg` (`id_member`,`approved`,`id_msg`),
  KEY `idx_current_topic` (`id_topic`,`id_msg`,`id_member`,`approved`),
  KEY `idx_related_ip` (`id_member`,`poster_ip`,`id_msg`),
  KEY `idx_likes` (`likes`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_messages`
--

LOCK TABLES `smf_messages` WRITE;
/*!40000 ALTER TABLE `smf_messages` DISABLE KEYS */;
INSERT INTO `smf_messages` VALUES
(1,1,1,1788664492,0,1,'Welcome to SMF!','Simple Machines','info@simplemachines.org',NULL,1,0,'','','Welcome to Simple Machines Forum!<br><br>We hope you enjoy using your forum.&nbsp; If you have any problems, please feel free to [url=https://www.simplemachines.org/community/index.php]ask us for assistance[/url].<br><br>Thanks!<br>Simple Machines','xx',1,0);
/*!40000 ALTER TABLE `smf_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_moderator_groups`
--

DROP TABLE IF EXISTS `smf_moderator_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_moderator_groups` (
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_group` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_board`,`id_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_moderators`
--

DROP TABLE IF EXISTS `smf_moderators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_moderators` (
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_board`,`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_moderators`
--

LOCK TABLES `smf_moderators` WRITE;
/*!40000 ALTER TABLE `smf_moderators` DISABLE KEYS */;
/*!40000 ALTER TABLE `smf_moderators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_moods`
--

DROP TABLE IF EXISTS `smf_moods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_moods` (
  `id_mood` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL DEFAULT '',
  `emoji` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `active` tinyint(3) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_mood`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_moods`
--

LOCK TABLES `smf_moods` WRITE;
/*!40000 ALTER TABLE `smf_moods` DISABLE KEYS */;
INSERT INTO `smf_moods` VALUES
(1,'Happy','&#128522;','Feeling joyful and content',1,1),
(2,'Excited','&#129321;','Full of enthusiasm and energy',2,1),
(3,'Relaxed','&#128524;','Calm and at ease',3,1),
(4,'Thoughtful','&#129300;','Deep in thought',4,1),
(5,'Sad','&#128546;','Feeling a bit down',5,1),
(6,'Angry','&#128544;','Feeling frustrated or irritated',6,1),
(7,'Sleepy','&#128564;','Tired and ready for rest',7,1),
(8,'Silly','&#129322;','In a goofy, playful mood',8,1),
(9,'Confused','&#128533;','Not quite sure what is going on',9,1),
(10,'Awesome','&#128526;','Feeling cool and confident',10,1);
/*!40000 ALTER TABLE `smf_moods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_package_servers`
--

DROP TABLE IF EXISTS `smf_package_servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_package_servers` (
  `id_server` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `validation_url` varchar(255) NOT NULL DEFAULT '',
  `extra` text DEFAULT NULL,
  PRIMARY KEY (`id_server`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_package_servers`
--

LOCK TABLES `smf_package_servers` WRITE;
/*!40000 ALTER TABLE `smf_package_servers` DISABLE KEYS */;
INSERT INTO `smf_package_servers` VALUES
(1,'Simple Machines Third-party Mod Site','https://custom.simplemachines.org/packages/mods','https://custom.simplemachines.org/api.php?action=validate;version=v1;smf_version={SMF_VERSION}',NULL),
(2,'Simple Machines Downloads Site','https://download.simplemachines.org/browse.php?api=v1;smf_version={SMF_VERSION}','https://download.simplemachines.org/validate.php?api=v1;smf_version={SMF_VERSION}',NULL);
/*!40000 ALTER TABLE `smf_package_servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_permission_profiles`
--

DROP TABLE IF EXISTS `smf_permission_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_permission_profiles` (
  `id_profile` smallint(6) NOT NULL AUTO_INCREMENT,
  `profile_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_profile`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_permission_profiles`
--

LOCK TABLES `smf_permission_profiles` WRITE;
/*!40000 ALTER TABLE `smf_permission_profiles` DISABLE KEYS */;
INSERT INTO `smf_permission_profiles` VALUES
(1,'default'),
(2,'no_polls'),
(3,'reply_only'),
(4,'read_only');
/*!40000 ALTER TABLE `smf_permission_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_permissions`
--

DROP TABLE IF EXISTS `smf_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_permissions` (
  `id_group` smallint(6) NOT NULL DEFAULT 0,
  `permission` varchar(30) NOT NULL DEFAULT '',
  `add_deny` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_group`,`permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_permissions`
--

LOCK TABLES `smf_permissions` WRITE;
/*!40000 ALTER TABLE `smf_permissions` DISABLE KEYS */;
INSERT INTO `smf_permissions` VALUES
(-1,'arcade_play',1),
(-1,'arcade_view',1),
(-1,'calendar_view',1),
(-1,'profile_view',1),
(-1,'search_posts',1),
(-1,'view_mlist',1),
(-1,'view_stats',1),
(0,'arcade_comment_own',1),
(0,'arcade_create_match',1),
(0,'arcade_edit_settings_own',1),
(0,'arcade_join_invite_match',1),
(0,'arcade_join_match',1),
(0,'arcade_online',1),
(0,'arcade_play',1),
(0,'arcade_report',1),
(0,'arcade_submit',1),
(0,'arcade_user_stats_any',1),
(0,'arcade_user_stats_own',1),
(0,'arcade_view',1),
(0,'arcade_view_arena',1),
(0,'calendar_view',1),
(0,'mention',1),
(0,'pm_draft',1),
(0,'pm_read',1),
(0,'pm_send',1),
(0,'profile_blurb_own',1),
(0,'profile_displayed_name_own',1),
(0,'profile_extra_own',1),
(0,'profile_forum_own',1),
(0,'profile_identity_own',1),
(0,'profile_password_own',1),
(0,'profile_remote_avatar',1),
(0,'profile_remove_own',1),
(0,'profile_server_avatar',1),
(0,'profile_signature_own',1),
(0,'profile_upload_avatar',1),
(0,'profile_view',1),
(0,'profile_website_own',1),
(0,'search_posts',1),
(0,'send_email_to_members',1),
(0,'shop_canAccess',1),
(0,'shop_canBank',1),
(0,'shop_canBuy',1),
(0,'shop_viewInventory',1),
(0,'view_mlist',1),
(0,'view_stats',1),
(0,'who_view',1),
(2,'access_mod_center',1),
(2,'arcade_comment_own',1),
(2,'arcade_create_match',1),
(2,'arcade_edit_settings_own',1),
(2,'arcade_join_invite_match',1),
(2,'arcade_join_match',1),
(2,'arcade_online',1),
(2,'arcade_play',1),
(2,'arcade_report',1),
(2,'arcade_submit',1),
(2,'arcade_user_stats_any',1),
(2,'arcade_user_stats_own',1),
(2,'arcade_view',1),
(2,'arcade_view_arena',1),
(2,'calendar_edit_any',1),
(2,'calendar_post',1),
(2,'calendar_view',1),
(2,'mention',1),
(2,'pm_draft',1),
(2,'pm_read',1),
(2,'pm_send',1),
(2,'profile_blurb_own',1),
(2,'profile_displayed_name_own',1),
(2,'profile_extra_own',1),
(2,'profile_forum_own',1),
(2,'profile_identity_own',1),
(2,'profile_password_own',1),
(2,'profile_remote_avatar',1),
(2,'profile_remove_own',1),
(2,'profile_server_avatar',1),
(2,'profile_signature_own',1),
(2,'profile_title_own',1),
(2,'profile_upload_avatar',1),
(2,'profile_view',1),
(2,'profile_website_own',1),
(2,'search_posts',1),
(2,'send_email_to_members',1),
(2,'shop_canAccess',1),
(2,'shop_canBank',1),
(2,'shop_canBuy',1),
(2,'shop_viewInventory',1),
(2,'view_mlist',1),
(2,'view_stats',1),
(2,'who_view',1),
(10,'arcade_comment_own',1),
(10,'arcade_create_match',1),
(10,'arcade_edit_settings_own',1),
(10,'arcade_join_invite_match',1),
(10,'arcade_join_match',1),
(10,'arcade_online',1),
(10,'arcade_play',1),
(10,'arcade_report',1),
(10,'arcade_submit',1),
(10,'arcade_user_stats_any',1),
(10,'arcade_user_stats_own',1),
(10,'arcade_view',1),
(10,'arcade_view_arena',1),
(10,'calendar_view',1),
(10,'mention',1),
(10,'pm_draft',1),
(10,'pm_read',1),
(10,'pm_send',1),
(10,'profile_blurb_own',1),
(10,'profile_displayed_name_own',1),
(10,'profile_extra_own',1),
(10,'profile_forum_own',1),
(10,'profile_identity_own',1),
(10,'profile_password_own',1),
(10,'profile_remote_avatar',1),
(10,'profile_remove_own',1),
(10,'profile_server_avatar',1),
(10,'profile_signature_own',1),
(10,'profile_upload_avatar',1),
(10,'profile_view',1),
(10,'profile_website_own',1),
(10,'search_posts',1),
(10,'send_email_to_members',1),
(10,'shop_canAccess',1),
(10,'shop_canBank',1),
(10,'shop_canBuy',1),
(10,'shop_viewInventory',1),
(10,'view_mlist',1),
(10,'view_stats',1),
(10,'who_view',1);
/*!40000 ALTER TABLE `smf_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_personal_messages`
--

DROP TABLE IF EXISTS `smf_personal_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_personal_messages` (
  `id_pm` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_pm_head` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member_from` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `deleted_by_sender` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `from_name` varchar(255) NOT NULL DEFAULT '',
  `msgtime` int(10) unsigned NOT NULL DEFAULT 0,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  PRIMARY KEY (`id_pm`),
  KEY `idx_id_member` (`id_member_from`,`deleted_by_sender`),
  KEY `idx_msgtime` (`msgtime`),
  KEY `idx_id_pm_head` (`id_pm_head`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_pm_labeled_messages`
--

DROP TABLE IF EXISTS `smf_pm_labeled_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_pm_labeled_messages` (
  `id_label` int(10) unsigned NOT NULL DEFAULT 0,
  `id_pm` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_label`,`id_pm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_pm_labels`
--

DROP TABLE IF EXISTS `smf_pm_labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_pm_labels` (
  `id_label` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `name` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_pm_recipients`
--

DROP TABLE IF EXISTS `smf_pm_recipients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_pm_recipients` (
  `id_pm` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `bcc` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `is_read` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `is_new` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `in_inbox` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_pm`,`id_member`),
  UNIQUE KEY `idx_id_member` (`id_member`,`deleted`,`id_pm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_pm_rules`
--

DROP TABLE IF EXISTS `smf_pm_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_pm_rules` (
  `id_rule` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `rule_name` varchar(60) NOT NULL,
  `criteria` text NOT NULL,
  `actions` text NOT NULL,
  `delete_pm` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `is_or` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_rule`),
  KEY `idx_id_member` (`id_member`),
  KEY `idx_delete_pm` (`delete_pm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_poll_choices`
--

DROP TABLE IF EXISTS `smf_poll_choices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_poll_choices` (
  `id_poll` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_choice` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `label` varchar(255) NOT NULL DEFAULT '',
  `votes` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_poll`,`id_choice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_polls`
--

DROP TABLE IF EXISTS `smf_polls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_polls` (
  `id_poll` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL DEFAULT '',
  `voting_locked` tinyint(4) NOT NULL DEFAULT 0,
  `max_votes` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `expire_time` int(10) unsigned NOT NULL DEFAULT 0,
  `hide_results` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `change_vote` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `guest_vote` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `num_guest_voters` int(10) unsigned NOT NULL DEFAULT 0,
  `reset_poll` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `poster_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_poll`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_qanda`
--

DROP TABLE IF EXISTS `smf_qanda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_qanda` (
  `id_question` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `lngfile` varchar(255) NOT NULL DEFAULT '',
  `question` varchar(255) NOT NULL DEFAULT '',
  `answers` text NOT NULL,
  PRIMARY KEY (`id_question`),
  KEY `idx_lngfile` (`lngfile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_scheduled_tasks`
--

DROP TABLE IF EXISTS `smf_scheduled_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_scheduled_tasks` (
  `id_task` smallint(6) NOT NULL AUTO_INCREMENT,
  `next_time` int(11) NOT NULL DEFAULT 0,
  `time_offset` int(11) NOT NULL DEFAULT 0,
  `time_regularity` smallint(6) NOT NULL DEFAULT 0,
  `time_unit` varchar(1) NOT NULL DEFAULT 'h',
  `disabled` tinyint(4) NOT NULL DEFAULT 0,
  `task` varchar(24) NOT NULL DEFAULT '',
  `callable` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_task`),
  UNIQUE KEY `idx_task` (`task`),
  KEY `idx_next_time` (`next_time`),
  KEY `idx_disabled` (`disabled`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_scheduled_tasks`
--

LOCK TABLES `smf_scheduled_tasks` WRITE;
/*!40000 ALTER TABLE `smf_scheduled_tasks` DISABLE KEYS */;
INSERT INTO `smf_scheduled_tasks` VALUES
(3,1788825660,60,1,'d',0,'daily_maintenance',''),
(5,1788825600,0,1,'d',0,'daily_digest',''),
(6,1789257600,0,1,'w',0,'weekly_digest',''),
(7,1788752880,100087,1,'d',0,'fetchSMfiles',''),
(8,0,0,1,'d',1,'birthdayemails',''),
(9,1789257600,0,1,'w',0,'weekly_maintenance',''),
(10,0,120,1,'d',1,'paid_subscriptions',''),
(11,1788825720,120,1,'d',0,'remove_temp_attachments',''),
(12,1788825780,180,1,'d',0,'remove_topic_redirect',''),
(13,1788825840,240,1,'d',0,'remove_old_drafts',''),
(14,0,0,1,'w',1,'prune_log_topics',''),
(15,1788825600,0,1,'d',0,'shop_bank_interest','Shop\\Tasks\\Scheduled::bank_interest#');
/*!40000 ALTER TABLE `smf_scheduled_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_sessions`
--

DROP TABLE IF EXISTS `smf_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_sessions` (
  `session_id` varchar(128) NOT NULL DEFAULT '',
  `last_update` int(10) unsigned NOT NULL DEFAULT 0,
  `data` text NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_settings`
--

DROP TABLE IF EXISTS `smf_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_settings` (
  `variable` varchar(255) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`variable`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_settings`
--

LOCK TABLES `smf_settings` WRITE;
/*!40000 ALTER TABLE `smf_settings` DISABLE KEYS */;
INSERT INTO `smf_settings` VALUES
('additional_options_collapsable','1'),
('adminlog_enabled','1'),
('alerts_auto_purge','30'),
('allow_editDisplayName','1'),
('allow_expire_redirect','1'),
('allow_guestAccess','1'),
('allow_hideOnline','1'),
('allow_ignore_boards','1'),
('attachmentCheckExtensions','0'),
('attachmentDirFileLimit','1000'),
('attachmentDirSizeLimit','10240'),
('attachmentEnable','1'),
('attachmentExtensions','doc,gif,jpg,mpg,pdf,png,txt,zip'),
('attachmentNumPerPostLimit','4'),
('attachmentPostLimit','192'),
('attachmentShowImages','1'),
('attachmentSizeLimit','128'),
('attachments_21_done','1'),
('attachmentThumbHeight','150'),
('attachmentThumbnails','1'),
('attachmentThumbWidth','150'),
('attachmentUploadDir','{\"1\":\"/srv/dumb/attachments\"}'),
('attachment_image_paranoid','0'),
('attachment_image_reencode','1'),
('attachment_thumb_png','1'),
('autoFixDatabase','1'),
('autoLinkUrls','1'),
('avatar_action_too_large','option_css_resize'),
('avatar_directory','/srv/dumb/avatars'),
('avatar_download_png','1'),
('avatar_max_height_external','65'),
('avatar_max_height_upload','65'),
('avatar_max_width_external','65'),
('avatar_max_width_upload','65'),
('avatar_paranoid','0'),
('avatar_reencode','1'),
('avatar_resize_upload','1'),
('avatar_url','http://localhost:4000/avatars'),
('banLastUpdated','0'),
('bcrypt_hash_cost','13'),
('birthday_email','happy_birthday'),
('boardindex_max_depth','5'),
('board_manager_groups','1'),
('browser_cache','1788710755'),
('calendar_updated','1788729492'),
('cal_daysaslink','0'),
('cal_days_for_index','7'),
('cal_defaultboard',''),
('cal_disable_prev_next','0'),
('cal_display_type','0'),
('cal_enabled','0'),
('cal_maxspan','0'),
('cal_maxyear','2030'),
('cal_minyear','2008'),
('cal_prev_next_links','1'),
('cal_short_days','0'),
('cal_short_months','0'),
('cal_showbdays','1'),
('cal_showevents','1'),
('cal_showholidays','1'),
('cal_showInTopic','1'),
('cal_week_links','2'),
('censorIgnoreCase','1'),
('censor_proper',''),
('censor_vulgar',''),
('compactTopicPagesContiguous','5'),
('compactTopicPagesEnable','1'),
('cookieTime','3153600'),
('currentAttachmentUploadDir','1'),
('custom_avatar_dir','/srv/dumb/custom_avatar'),
('custom_avatar_url','http://localhost:4000/custom_avatar'),
('databaseSession_enable','1'),
('databaseSession_lifetime','2880'),
('databaseSession_loose','1'),
('defaultMaxListItems','15'),
('defaultMaxMembers','30'),
('defaultMaxMessages','15'),
('defaultMaxTopics','20'),
('default_personal_text',''),
('default_timezone','UTC'),
('disabledBBC','acronym,bdo,black,blue,flash,ftp,glow,green,move,red,shadow,tt,white'),
('displayFields','[{\"col_name\":\"cust_skype\",\"title\":\"{skype}\",\"type\":\"text\",\"order\":\"1\",\"bbc\":\"0\",\"placement\":\"1\",\"enclose\":\"<a href=\\\"skype:{INPUT}?call\\\"><img src=\\\"{DEFAULT_IMAGES_URL}\\/skype.png\\\" alt=\\\"{INPUT}\\\" title=\\\"{INPUT}\\\" \\/><\\/a> \",\"mlist\":\"0\",\"options\":[]},{\"col_name\":\"cust_loca\",\"title\":\"{location}\",\"type\":\"text\",\"order\":\"3\",\"bbc\":\"0\",\"placement\":\"0\",\"enclose\":\"\",\"mlist\":\"0\",\"options\":[]},{\"col_name\":\"cust_gender\",\"title\":\"{gender}\",\"type\":\"radio\",\"order\":\"4\",\"bbc\":\"0\",\"placement\":\"1\",\"enclose\":\"<span class=\\\" main_icons gender_{KEY}\\\" title=\\\"{INPUT}\\\"><\\/span>\",\"mlist\":\"0\",\"options\":[\"{gender_0}\",\"{gender_1}\",\"{gender_2}\"]}]'),
('dont_repeat_buddylists','1'),
('dont_repeat_smileys_20','1'),
('dont_repeat_theme_core','1'),
('drafts_autosave_enabled','1'),
('drafts_keep_days','7'),
('drafts_pm_enabled','1'),
('drafts_post_enabled','1'),
('drafts_show_saved_enabled','1'),
('edit_disable_time','0'),
('edit_wait_time','90'),
('enableAllMessages','0'),
('enableBBC','1'),
('enableCompressedOutput','0'),
('enableErrorLogging','1'),
('enableErrorQueryLogging','1'),
('enableParticipation','1'),
('enablePostHTML','0'),
('enablePreviousNext','1'),
('enableThemes','1,2'),
('enable_ajax_alerts','1'),
('enable_buddylist','1'),
('enable_mentions','1'),
('export_dir','/srv/dumb/exports'),
('export_expiry','7'),
('export_min_diskspace_pct','5'),
('export_rate','250'),
('failed_login_threshold','3'),
('force_ssl','0'),
('global_character_set','UTF-8'),
('gravatarAllowExtraEmail','1'),
('gravatarEnabled','1'),
('gravatarMaxRating','PG'),
('gravatarOverride','0'),
('hitStats','1'),
('httponlyCookies','1'),
('integrate_actions','Arcade_actions,$sourcedir/MoodMod.php|MoodMod_actions'),
('integrate_admin_areas','Arcade_admin_areas,$sourcedir/MoodMod.php|MoodMod_admin_areas'),
('integrate_admin_search','Arcade_admin_search'),
('integrate_core_features','Arcade_core_features'),
('integrate_load_custom_profile_fields','arcade_custom_profile'),
('integrate_load_permissions','Arcade_load_permissions'),
('integrate_load_theme','Arcade_load_theme,$sourcedir/MoodMod.php|MoodMod_load_theme'),
('integrate_memberContext','$sourcedir/MoodMod.php|MoodMod_memberContext'),
('integrate_menu_buttons','$sourcedir/MoodMod.php|MoodMod_menu_buttons'),
('integrate_pre_include','$sourcedir/ArcadeHooks.php'),
('integrate_pre_load','$sourcedir/Shop/Shop.php|Shop\\Shop::initialize,Arcade_load_language,$sourcedir/Class-Spoiler.php|Spoiler::hooks#'),
('integrate_pre_log_stats','Arcade_game_support'),
('integrate_pre_profile_areas','Arcade_profile_areas'),
('integrate_profile_areas','$sourcedir/MoodMod.php|MoodMod_profile_areas'),
('integrate_profile_popup','$sourcedir/MoodMod.php|MoodMod_profile_popup'),
('integrate_theme_context','Arcade_menu_buttons'),
('integrate_validateSession','Arcade_validate_session'),
('integrate_viewModLog','Arcade_viewModLog'),
('integrate_whos_online','Arcade_whos_online'),
('jquery_source','local'),
('json_done','1'),
('knownThemes','1,2'),
('lastActive','15'),
('last_mod_report_action','0'),
('latestMember','11'),
('latestRealName','admin'),
('loginHistoryDays','30'),
('mail_limit','5'),
('mail_next_send','0'),
('mail_quantity','5'),
('mail_recent','1788734165|5'),
('mail_type','0'),
('mark_read_beyond','90'),
('mark_read_delete_beyond','365'),
('mark_read_max_users','500'),
('maxMsgID','10'),
('max_image_height','0'),
('max_image_width','0'),
('max_messageLength','20000'),
('memberlist_updated','1788731458'),
('minimize_files','1'),
('modlog_enabled','1'),
('mostDate','1788715652'),
('mostOnline','28'),
('mostOnlineToday','6'),
('mostOnlineUpdated','2026-09-07'),
('news','SMF - Just Installed!'),
('next_task_time','1788752880'),
('number_format','1234.00'),
('oldTopicDays','120'),
('onlineEnable','0'),
('package_make_backups','1'),
('permission_enable_deny','0'),
('permission_enable_postgroups','0'),
('pm_spam_settings','10,5,20'),
('pollMode','1'),
('pruningOptions','30,180,180,180,30,0'),
('queryless_urls','1'),
('rand_seed','1788744996.9397'),
('recaptcha_theme','light'),
('recycle_board','0'),
('recycle_enable','0'),
('registration_method','2'),
('reg_verification','1'),
('requireAgreement','1'),
('requirePolicyAgreement','0'),
('reserveCase','1'),
('reserveName','1'),
('reserveNames','Admin\nWebmaster\nGuest\nroot'),
('reserveUser','1'),
('reserveWord','0'),
('samesiteCookies','lax'),
('search_cache_size','50'),
('search_floodcontrol_time','5'),
('search_max_results','1200'),
('search_pointer','3'),
('search_results_per_page','30'),
('search_weight_age','25'),
('search_weight_first_message','10'),
('search_weight_frequency','30'),
('search_weight_length','20'),
('search_weight_subject','15'),
('securityDisable_moderate','1'),
('send_validation_onChange','0'),
('send_welcomeEmail','1'),
('settings_updated','1788745003'),
('Shop_credits_suffix','D$'),
('Shop_enable_games','1'),
('Shop_enable_shop','1'),
('Shop_enable_stats','1'),
('Shop_importer_success','0'),
('show_blurb','1'),
('show_modify','1'),
('show_profile_buttons','1'),
('show_user_images','1'),
('signature_settings','1,300,0,0,0,0,0,0:'),
('smfVersion','2.1.7'),
('smileys_dir','/srv/dumb/Smileys'),
('smileys_url','http://localhost:4000/Smileys'),
('smiley_sets_default','fugue'),
('smiley_sets_known','fugue,alienine'),
('smiley_sets_names','Fugue''s Set\nAlienine''s Set'),
('smtp_host',''),
('smtp_password','TjZOS3plU1RmUDMzSlh4R29Ya3BHal5AWW4kWUE4OCQ='),
('smtp_port',''),
('smtp_username',''),
('spamWaitTime','5'),
('tfa_mode','1'),
('theme_allow','1'),
('theme_default','1'),
('theme_guests','1'),
('timeLoadPageEnable','0'),
('time_format','%b %d, %Y, %I:%M %p'),
('titlesEnable','1'),
('tld_regex','(?>சிங்கப்பூர்|پاکستان|فلسطين|ファッション|ישראל|همراه|संगठन|বাংলা|భారత్|ഭാരതം|дети|تونس|شبكة|ڀارت|ਭਾਰਤ|ભારત|ଭାରତ|ಭಾರತ|ලංකා|アマゾン|クラウド|グーグル|ポイント|组织机构|電訊盈科|укр|қаз|հայ|קום|قطر|कॉम|नेट|भार(?>ोत|त(?>म्|))|คอม|ไทย|ລາວ|みんな|ストア|セール|亚马逊|天主教|我爱你|淡马锡|飞利浦|ею|سو(?>دان|رية)|ভা(?>রত|ৰত)|გე|コム|世界|企业|佛山|信息|健康|八卦|嘉里(?>大酒店|)|在线|大拿|娱乐|家電|广东|微博|慈善|手机|招聘|时尚|書籍|机构|游戏|澳門|点看|移动|联通|谷歌|购物|通販|集团|食品|餐厅|삼성|한국|a(?>kdn|a(?>rp|a)|b(?>udhabi|ogado|le|b(?>ott|vie|)|c)|c(?>ademy|tor|c(?>ountant(?>s|)|enture)|o|)|d(?>ult|s|)|e(?>tna|ro|g|)|f(?>rica|l|)|g(?>akhan|ency|)|i(?>g|r(?>force|bus|tel)|)|l(?>i(?>baba|pay)|l(?>finanz|state|y)|s(?>ace|tom)|)|m(?>sterdam|azon|fam|ica|e(?>rican(?>express|family)|x)|)|n(?>alytics|droid|quan|z)|o(?>l|)|p(?>artments|p(?>le|))|q(?>uarelle|)|r(?>chi|my|pa|a(?>mco|b)|t(?>e|)|)|s(?>sociates|da|ia|)|t(?>torney|hleta|)|u(?>ction|spost|di(?>ble|o|)|t(?>hor|o(?>s|))|)|w(?>s|)|x(?>a|)|z(?>ure|))|b(?>a(?>uhaus|yern|idu|by|n(?>amex|d|k)|r(?>efoot|gains|c(?>elona|lay(?>card|s))|)|s(?>ketball|eball)|)|b(?>va|c|t|)|c(?>g|n)|d|e(?>rlin|er|st(?>buy|)|a(?>uty|ts)|t|)|f|g|h(?>arti|)|i(?>ble|ke|ng(?>o|)|d|o|z|)|j|l(?>ack(?>friday|)|ue|o(?>ckbuster|omberg|g))|m(?>s|w|)|n(?>pparibas|)|o(?>ehringer|utique|ats|fa|nd|m|o(?>k(?>ing|)|)|s(?>ch|t(?>ik|on))|t|x|)|r(?>idgestone|adesco|ussels|o(?>adway|ther|ker)|)|s|t|u(?>siness|ild(?>ers|)|zz|y)|v|w|y|z(?>h|))|c(?>pa|a(?>non|fe|b|l(?>vinklein|l|)|m(?>era|p|)|p(?>etown|ital(?>one|))|r(?>avan|ds|e(?>er(?>s|)|)|s|)|s(?>ino|a|e|h)|t(?>ering|holic|)|)|b(?>re|a|n)|c|d|e(?>nter|rn|o)|f(?>a|d|)|g|h(?>intai|urch|eap|a(?>rity|se|n(?>nel|el)|t)|r(?>istmas|ome)|)|i(?>priani|rcle|sco|t(?>adel|i(?>c|)|y)|)|k|l(?>eaning|aims|ub(?>med|)|i(?>ck|ni(?>que|c))|o(?>thing|ud)|)|m|n|o(?>rsica|ffee|ach|des|l(?>lege|ogne)|m(?>sec|m(?>unity|bank)|p(?>uter|a(?>ny|re))|)|n(?>dos|s(?>truction|ulting)|t(?>ractors|act))|o(?>king|l|p)|u(?>ntry|rses|pon(?>s|))|)|r(?>icket|edit(?>union|card|)|uise(?>s|)|own|s|)|u(?>isinella|)|v|w|x|y(?>mru|ou|)|z)|d(?>rive|clk|ds|hl|np|tv|a(?>nce|d|t(?>ing|sun|a|e)|y)|e(?>mocrat|gree|al(?>er|s|)|nt(?>ist|al)|si(?>gn|)|l(?>ivery|oitte|ta|l)|v|)|i(?>amonds|gital|rect(?>ory|)|et|s(?>co(?>unt|ver)|h)|y)|j|k|m|o(?>wnload|mains|c(?>tor|s)|g|t|)|u(?>pont|rban|bai)|v(?>ag|r)|z)|e(?>quipment|vents|pson|a(?>rth|t)|c(?>o|)|d(?>eka|u(?>cation|))|e|g|m(?>erck|ail)|n(?>terprises|gineer(?>ing|)|ergy)|r(?>icsson|ni|)|s(?>tate|q|)|t|u(?>rovision|s|)|x(?>traspace|change|p(?>osed|ress|ert)))|f(?>tr|yi|a(?>mily|ge|rm(?>ers|)|i(?>rwinds|th|l)|n(?>s|)|s(?>hion|t))|e(?>edback|dex|rr(?>ari|ero))|i(?>lm|na(?>nc(?>ial|e)|l)|sh(?>ing|)|d(?>elity|o)|r(?>mdale|e(?>stone|))|t(?>ness|)|)|j|k|l(?>i(?>ghts|ckr|r)|o(?>rist|wers)|y)|m|o(?>undation|o(?>tball|d|)|r(?>sale|ex|um|d)|x|)|r(?>e(?>senius|e)|l|o(?>ntier|gans)|)|u(?>rniture|jitsu|tbol|n(?>d|)))|g(?>a(?>rden|me(?>s|)|l(?>l(?>ery|up|o)|)|p|y|)|b(?>iz|)|d(?>n|)|e(?>orge|nt(?>ing|)|a|)|f|g(?>ee|)|h|i(?>ft(?>s|)|v(?>ing|es)|)|l(?>ass|ob(?>al|o)|e|)|m(?>ail|bh|o|x|)|n|o(?>daddy|l(?>d(?>point|)|f)|o(?>dyear|g(?>le|))|p|t|v)|p|q|r(?>een|ipe|a(?>inger|phics|tis)|o(?>cery|up)|)|s|t|u(?>cci|ge|ru|i(?>tars|de)|)|w|y)|h(?>dfc(?>bank|)|sbc|bo|a(?>mburg|ngout|ir|us)|e(?>alth(?>care|)|l(?>sinki|p)|r(?>mes|e))|i(?>samitsu|tachi|phop|v)|k(?>t|)|m|n|o(?>ckey|nda|rse|use|me(?>depot|goods|s(?>ense|))|l(?>dings|iday)|s(?>pital|t(?>ing|))|t(?>mail|els|)|w)|r|t|u(?>ghes|)|y(?>undai|att))|i(?>piranga|kano|bm|fm|c(?>bc|e|u)|d|e(?>ee|)|l|m(?>amat|db|mo(?>bilien|)|)|n(?>vestments|dustries|c|f(?>initi|o)|g|k|s(?>titute|ur(?>ance|e))|t(?>ernational|uit|)|)|o|q|r(?>ish|)|s(?>maili|t(?>anbul|)|)|t(?>au|v|))|j(?>cb|io|ll|nj|a(?>guar|va)|e(?>welry|tzt|ep|)|m(?>p|)|o(?>b(?>urg|s)|t|y|)|p(?>morgan|rs|)|u(?>niper|egos))|k(?>uokgroup|aufen|ddi|fh|e(?>rry(?>properties|hotels)|)|g|h|i(?>tchen|ndle|ds|wi|a|m|)|m|n|o(?>matsu|sher|eln)|p(?>mg|n|)|r(?>ed|d|)|w|y(?>oto|)|z)|l(?>gbt|ds|pl(?>financial|)|a(?>caixa|salle|m(?>borghini|er)|n(?>xess|d(?>rover|))|t(?>robe|ino|)|w(?>yer|)|)|b|c|e(?>clerc|frak|ase|xus|g(?>al|o))|i(?>ghting|lly|dl|fe(?>insurance|style|)|ke|m(?>ited|o)|n(?>coln|k)|v(?>ing|e)|)|k|l(?>c|p)|o(?>ndon|an(?>s|)|tt(?>e|o)|ve|c(?>ker|al|us)|l)|r|s|t(?>d(?>a|)|)|u(?>ndbeck|x(?>ury|e)|)|v|y)|m(?>ba|a(?>drid|keup|ttel|i(?>son|f)|n(?>agement|go|)|p|r(?>shalls|riott|ket(?>ing|s|))|)|c(?>kinsey|)|d|e(?>lbourne|rck(?>msd|)|et|d(?>ia|)|m(?>orial|e)|n(?>u|)|)|g|h|i(?>crosoft|ami|l|n(?>i|t)|t(?>subishi|))|k|l(?>b|s|)|m(?>a|)|n|o(?>scow|bi(?>le|)|da|to(?>rcycles|)|e|i|m|n(?>ster|ash|ey)|r(?>tgage|mon)|v(?>ie|)|)|p|q|r|s(?>d|)|t(?>n|r|)|u(?>s(?>eum|ic)|)|v|w|x|y|z)|n(?>ba|hk|tt|yc|a(?>goya|me|vy|b|)|c|e(?>ustar|c|t(?>bank|flix|work|)|w(?>s|)|x(?>us|t(?>direct|))|)|f(?>l|)|g(?>o|)|i(?>nja|ssa(?>n|y)|co|k(?>on|e)|)|l|o(?>rton|kia|w(?>ruz|tv|)|)|p|r(?>a|w|)|u|z)|o(?>kinawa|ffice|saka|pen|oo|vh|b(?>server|i)|l(?>ayan(?>group|)|lo)|m(?>ega|)|n(?>ion|e|g|l(?>ine|))|r(?>igins|a(?>cle|nge)|g(?>anic|))|t(?>suka|t))|p(?>ccw|ub|a(?>nasonic|ge|r(?>is|s|t(?>ners|s|y))|y|)|e(?>t|)|f(?>izer|)|g|h(?>armacy|ilips|ysio|d|o(?>ne|to(?>graphy|s|))|)|i(?>oneer|zza|c(?>s|t(?>ures|et))|d|n(?>g|k|))|k|l(?>a(?>ce|y(?>station|))|u(?>mbing|s)|)|m|n(?>c|)|o(?>litie|ker|hl|rn|st)|r(?>axi|ess|ime|o(?>gressive|tection|pert(?>ies|y)|mo|d(?>uctions|)|f|)|u(?>dential|)|)|s|t|w(?>c|)|y)|q(?>pon|ue(?>bec|st)|a)|r(?>yukyu|a(?>cing|dio)|e(?>liance|cipes|xroth|view(?>s|)|hab|st(?>aurant|)|a(?>d|l(?>estate|t(?>or|y)))|d(?>umbrella|)|i(?>se(?>n|)|t)|n(?>t(?>als|)|)|p(?>ublican|air|ort)|)|i(?>c(?>oh|h(?>ardli|))|l|o|p)|o(?>gers|cks|deo|om|)|s(?>vp|)|u(?>gby|hr|n|)|w(?>e|))|s(?>fr|a(?>arland|kura|fe(?>ty|)|ms(?>club|ung)|rl|ve|xo|l(?>on|e)|n(?>dvik(?>coromant|)|ofi)|p|s|)|b(?>i|s|)|c(?>ience|ot|b|h(?>aeffler|midt|warz|ule|o(?>larships|ol))|)|d|e(?>rvices|lect|cur(?>ity|e)|ner|ven|ek|a(?>rch|t)|w|x(?>y|)|)|g|h(?>ell|a(?>ngrila|rp)|i(?>ksha|a)|o(?>uji|es|p(?>ping|)|w)|)|i(?>lk|te|n(?>gles|a)|)|j|k(?>i(?>n|)|y(?>pe|)|)|l(?>ing|)|m(?>art|ile|)|n(?>cf|)|o(?>ft(?>bank|ware)|hu|c(?>cer|ial)|l(?>utions|ar)|n(?>g|y)|y|)|p(?>a(?>ce|)|o(?>rt|t))|r(?>l|)|s|t(?>ream|yle|ud(?>io|y)|a(?>ples|da|te(?>bank|farm)|r)|c(?>group|)|o(?>ckholm|r(?>age|e))|)|u(?>zuki|cks|pp(?>ort|l(?>ies|y))|r(?>gery|f)|)|v|w(?>atch|iss)|x|y(?>stems|dney|)|z)|t(?>a(?>ipei|obao|rget|lk|b|t(?>too|a(?>motors|r))|x(?>i|))|c(?>i|)|d(?>k|)|e(?>masek|nnis|am|ch(?>nology|)|st|va|l)|f|g|h(?>eat(?>er|re)|d|)|i(?>ckets|enda|aa|ps|r(?>es|ol))|j(?>maxx|x|)|k(?>maxx|)|l|m(?>all|)|n|o(?>shiba|day|kyo|ols|ray|tal|urs|wn|p|y(?>ota|s)|)|r(?>ust|a(?>ining|vel(?>ers(?>insurance|)|)|d(?>ing|e))|v|)|t|u(?>nes|shu|be|i)|v(?>s|)|w|z)|u(?>ol|ps|a|b(?>ank|s)|g|k|n(?>i(?>versity|com)|o)|s|y|z)|v(?>laanderen|a(?>cations|n(?>guard|a)|)|c|e(?>ntures|gas|r(?>mögensberat(?>ung|er)|sicherung|isign)|t|)|g|i(?>ajes|king|llas|rgin|deo|g|n|p|s(?>ion|a)|v(?>a|o)|)|n|o(?>yage|dka|lvo|t(?>ing|e|o))|u)|w(?>hoswho|me|a(?>tch(?>es|)|ng(?>gou|)|l(?>mart|ter|es))|e(?>ather(?>channel|)|b(?>site|cam|er|)|d(?>ding|)|i(?>bo|r))|f|i(?>lliamhill|en|ki|n(?>dows|ners|e|))|o(?>odside|r(?>ld|k(?>s|))|w)|s|t(?>c|f))|x(?>erox|box|xx|yz|i(?>huan|n))|y(?>un|a(?>maxun|chts|ndex|hoo)|e|o(?>dobashi|kohama|ga|u(?>tube|))|t)|z(?>uerich|ero|one|ip|a(?>ppos|ra|)|m|w)|ε(?>λ|υ)|б(?>ел|г)|к(?>атолик|ом)|м(?>кд|о(?>сква|н))|о(?>нлайн|рг)|р(?>ус|ф)|с(?>айт|рб)|ا(?>بوظبي|رامكو|مارات|یران|ل(?>سعودية|بحرين|جزائر|عليان|اردن|مغرب))|ب(?>ھارت|يتك|ا(?>زار|رت))|ع(?>مان|ر(?>اق|ب))|ك(?>اثوليك|وم)|م(?>ليسيا|صر|و(?>ريتانيا|قع))|இ(?>ந்தியா|லங்கை)|中(?>文网|信|国|國)|公(?>司|益)|台(?>湾|灣)|商(?>城|店|标)|政(?>务|府)|新(?>加坡|闻)|网(?>址|店|站|络)|香(?>格里拉|港)|닷(?>넷|컴))'),
('todayMod','1'),
('topicSummaryPosts','15'),
('topic_move_any','0'),
('totalMembers','11'),
('totalMessages','10'),
('totalTopics','3'),
('trackStats','1'),
('unapprovedMembers','0'),
('userLanguage','1'),
('userlog_enabled','1'),
('use_subdirectories_for_attachments','1'),
('visual_verification_type','3'),
('warning_moderate','35'),
('warning_mute','60'),
('warning_settings','1,20,0'),
('warning_watch','10'),
('who_enabled','1'),
('xmlnews_enable','1'),
('xmlnews_maxlen','255');
/*!40000 ALTER TABLE `smf_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_smiley_files`
--

DROP TABLE IF EXISTS `smf_smiley_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_smiley_files` (
  `id_smiley` smallint(6) NOT NULL DEFAULT 0,
  `smiley_set` varchar(48) NOT NULL DEFAULT '',
  `filename` varchar(48) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_smiley`,`smiley_set`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_smiley_files`
--

LOCK TABLES `smf_smiley_files` WRITE;
/*!40000 ALTER TABLE `smf_smiley_files` DISABLE KEYS */;
INSERT INTO `smf_smiley_files` VALUES
(1,'alienine','smiley.png'),
(1,'fugue','smiley.png'),
(2,'alienine','wink.png'),
(2,'fugue','wink.png'),
(3,'alienine','cheesy.png'),
(3,'fugue','cheesy.png'),
(4,'alienine','grin.png'),
(4,'fugue','grin.png'),
(5,'alienine','angry.png'),
(5,'fugue','angry.png'),
(6,'alienine','sad.png'),
(6,'fugue','sad.png'),
(7,'alienine','shocked.png'),
(7,'fugue','shocked.png'),
(8,'alienine','cool.png'),
(8,'fugue','cool.png'),
(9,'alienine','huh.png'),
(9,'fugue','huh.png'),
(10,'alienine','rolleyes.png'),
(10,'fugue','rolleyes.png'),
(11,'alienine','tongue.png'),
(11,'fugue','tongue.png'),
(12,'alienine','embarrassed.png'),
(12,'fugue','embarrassed.png'),
(13,'alienine','lipsrsealed.png'),
(13,'fugue','lipsrsealed.png'),
(14,'alienine','undecided.png'),
(14,'fugue','undecided.png'),
(15,'alienine','kiss.png'),
(15,'fugue','kiss.png'),
(16,'alienine','cry.png'),
(16,'fugue','cry.png'),
(17,'alienine','evil.png'),
(17,'fugue','evil.png'),
(18,'alienine','azn.png'),
(18,'fugue','azn.png'),
(19,'alienine','afro.png'),
(19,'fugue','afro.png'),
(20,'alienine','laugh.png'),
(20,'fugue','laugh.png'),
(21,'alienine','police.png'),
(21,'fugue','police.png'),
(22,'alienine','angel.png'),
(22,'fugue','angel.png');
/*!40000 ALTER TABLE `smf_smiley_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_smileys`
--

DROP TABLE IF EXISTS `smf_smileys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_smileys` (
  `id_smiley` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL DEFAULT '',
  `description` varchar(80) NOT NULL DEFAULT '',
  `smiley_row` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `smiley_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `hidden` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_smiley`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_smileys`
--

LOCK TABLES `smf_smileys` WRITE;
/*!40000 ALTER TABLE `smf_smileys` DISABLE KEYS */;
INSERT INTO `smf_smileys` VALUES
(1,':)','Smiley',0,0,0),
(2,';)','Wink',0,1,0),
(3,':D','Cheesy',0,2,0),
(4,';D','Grin',0,3,0),
(5,'>:(','Angry',0,4,0),
(6,':(','Sad',0,5,0),
(7,':o','Shocked',0,6,0),
(8,'8)','Cool',0,7,0),
(9,'???','Huh?',0,8,0),
(10,'::)','Roll Eyes',0,9,0),
(11,':P','Tongue',0,10,0),
(12,':-[','Embarrassed',0,11,0),
(13,':-X','Lips Sealed',0,12,0),
(14,':-\\','Undecided',0,13,0),
(15,':-*','Kiss',0,14,0),
(16,':''(','Cry',0,15,0),
(17,'>:D','Evil',0,16,1),
(18,'^-^','Azn',0,17,1),
(19,'O0','Afro',0,18,1),
(20,':))','Laugh',0,19,1),
(21,'C:-)','Police',0,20,1),
(22,'O:-)','Angel',0,21,1);
/*!40000 ALTER TABLE `smf_smileys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_spiders`
--

DROP TABLE IF EXISTS `smf_spiders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_spiders` (
  `id_spider` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `spider_name` varchar(255) NOT NULL DEFAULT '',
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `ip_info` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_spider`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_spiders`
--

LOCK TABLES `smf_spiders` WRITE;
/*!40000 ALTER TABLE `smf_spiders` DISABLE KEYS */;
INSERT INTO `smf_spiders` VALUES
(1,'Google','googlebot',''),
(2,'Yahoo!','slurp',''),
(3,'Bing','bingbot',''),
(4,'Google (Mobile)','Googlebot-Mobile',''),
(5,'Google (Image)','Googlebot-Image',''),
(6,'Google (AdSense)','Mediapartners-Google',''),
(7,'Google (Adwords)','AdsBot-Google',''),
(8,'Yahoo! (Mobile)','YahooSeeker/M1A1-R2D2',''),
(9,'Yahoo! (Image)','Yahoo-MMCrawler',''),
(10,'Bing (Preview)','BingPreview',''),
(11,'Bing (Ads)','adidxbot',''),
(12,'Bing (MSNBot)','msnbot',''),
(13,'Bing (Media)','msnbot-media',''),
(14,'Cuil','twiceler',''),
(15,'Ask','Teoma',''),
(16,'Baidu','Baiduspider',''),
(17,'Gigablast','Gigabot',''),
(18,'InternetArchive','ia_archiver-web.archive.org',''),
(19,'Alexa','ia_archiver',''),
(20,'Omgili','omgilibot',''),
(21,'EntireWeb','Speedy Spider',''),
(22,'Yandex','yandex','');
/*!40000 ALTER TABLE `smf_spiders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_stshop_categories`
--

DROP TABLE IF EXISTS `smf_stshop_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_stshop_categories` (
  `catid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `image` tinytext NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`catid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_stshop_categories`
--

LOCK TABLES `smf_stshop_categories` WRITE;
/*!40000 ALTER TABLE `smf_stshop_categories` DISABLE KEYS */;
INSERT INTO `smf_stshop_categories` VALUES
(1,'Default','bookshelf.png','This is the default category');
/*!40000 ALTER TABLE `smf_stshop_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_stshop_inventory`
--

DROP TABLE IF EXISTS `smf_stshop_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_stshop_inventory` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `userid` mediumint(8) unsigned NOT NULL,
  `itemid` int(10) unsigned DEFAULT NULL,
  `trading` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `tradecost` int(10) unsigned NOT NULL DEFAULT 0,
  `date` int(10) unsigned NOT NULL,
  `tradedate` int(10) unsigned NOT NULL DEFAULT 0,
  `fav` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`,`userid`,`trading`),
  KEY `date_fav_tradedate` (`date`,`fav`,`tradedate`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_stshop_items`
--

DROP TABLE IF EXISTS `smf_stshop_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_stshop_items` (
  `itemid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `image` tinytext DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `price` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `stock` smallint(5) unsigned NOT NULL DEFAULT 0,
  `module` int(10) unsigned NOT NULL DEFAULT 0,
  `info1` int(11) DEFAULT 0,
  `info2` int(11) DEFAULT 0,
  `info3` int(11) DEFAULT 0,
  `info4` int(11) DEFAULT 0,
  `input_needed` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `can_use_item` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `delete_after_use` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `catid` int(10) unsigned NOT NULL DEFAULT 0,
  `status` smallint(5) unsigned NOT NULL DEFAULT 1,
  `itemlimit` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`itemid`),
  KEY `status_can_use_item_module_input_needed_stock` (`status`,`can_use_item`,`module`,`input_needed`,`stock`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_stshop_log_bank`
--

DROP TABLE IF EXISTS `smf_stshop_log_bank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_stshop_log_bank` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` mediumint(8) unsigned NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `fee` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `action` tinytext NOT NULL,
  `type` smallint(5) unsigned NOT NULL,
  `date` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`,`userid`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_stshop_log_buy`
--

DROP TABLE IF EXISTS `smf_stshop_log_buy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_stshop_log_buy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemid` int(10) unsigned NOT NULL,
  `invid` mediumint(8) unsigned NOT NULL,
  `userid` mediumint(8) unsigned NOT NULL,
  `sellerid` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `amount` int(10) unsigned NOT NULL,
  `fee` int(10) unsigned NOT NULL DEFAULT 0,
  `date` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`,`userid`,`itemid`),
  KEY `date_sellerid_invid` (`date`,`sellerid`,`invid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_stshop_log_content`
--

DROP TABLE IF EXISTS `smf_stshop_log_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_stshop_log_content` (
  `id_msg` int(10) unsigned NOT NULL,
  `id_member` mediumint(8) unsigned NOT NULL,
  `content` varchar(25) NOT NULL,
  PRIMARY KEY (`id_msg`,`id_member`,`content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_stshop_log_games`
--

DROP TABLE IF EXISTS `smf_stshop_log_games`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_stshop_log_games` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` mediumint(8) unsigned NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `game` tinytext NOT NULL,
  `date` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`,`userid`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_stshop_log_gift`
--

DROP TABLE IF EXISTS `smf_stshop_log_gift`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_stshop_log_gift` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` mediumint(8) unsigned NOT NULL,
  `receiver` mediumint(8) unsigned NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `itemid` mediumint(8) unsigned NOT NULL,
  `invid` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `message` varchar(255) NOT NULL,
  `is_admin` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `date` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`,`userid`,`receiver`),
  KEY `date_itemid_is_admin` (`date`,`itemid`,`is_admin`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_stshop_modules`
--

DROP TABLE IF EXISTS `smf_stshop_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_stshop_modules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `price` int(10) unsigned NOT NULL DEFAULT 0,
  `author` varchar(80) NOT NULL,
  `email` varchar(255) NOT NULL,
  `require_input` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `can_use_item` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `editable_input` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `web` varchar(255) NOT NULL,
  `file` tinytext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `can_use_item_require_input` (`can_use_item`,`require_input`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_stshop_modules`
--

LOCK TABLES `smf_stshop_modules` WRITE;
/*!40000 ALTER TABLE `smf_stshop_modules` DISABLE KEYS */;
INSERT INTO `smf_stshop_modules` VALUES
(1,'Increase Post Count','Increase the post count by ''x''',50,'Daniel15','dansoft@dansoftaustralia.net',0,1,1,'https://github.com/Daniel15','IncreasePostCount'),
(2,'Change Display Name','Change your display name',50,'Daniel15','dansoft@dansoftaustralia.net',1,1,1,'https://github.com/Daniel15','ChangeDisplayName'),
(3,'Random Money','Get a random amount of money betwen ''x'' and ''y''',75,'Daniel15','dansoft@dansoftaustralia.net',0,1,1,'https://github.com/Daniel15','RandomMoney'),
(4,'Steal Credits','Attempt to steal from another member',50,'Diego Andrés','admin@smftricks.com',1,1,1,'https://smftricks.com','Steal'),
(5,'Decrease Posts by xxx','Decrease <i>Someone else''s</i> post count by xxx!!',200,'Daniel15','dansoft@dansoftaustralia.net',1,1,1,'https://github.com/Daniel15','DecreasePost'),
(6,'Games Room Pass','Gives access to Games Room for ''x'' days',50,'Sleepy Arcade','wdm2005@blueyonder.co.uk',0,1,1,'https://www.simplemachines.org/community/index.php?action=profile;u=84438','GamesPass'),
(7,'Increase Total Time logged In','Increase your total time logged in by ''x'' hours',50,'Daniel15','dansoft@dansoftaustralia.net',0,1,1,'https://github.com/Daniel15','IncreaseTimeLoggedIn'),
(8,'Sticky Topic','Make any one of your topics a sticky',400,'Diego Andrés','admin@smftricks.com',1,0,1,'https://smftricks.com','StickyTopic'),
(9,'Change User Title','Allows you to change your title',50,'Daniel15','dansoft@dansoftaustralia.net',1,0,1,'https://github.com/Daniel15','ChangeUserTitle'),
(10,'Change Username','Change your username',50,'Daniel15','dansoft@dansoftaustralia.net',1,0,1,'https://github.com/Daniel15','ChangeUsername'),
(11,'Change Someone Else''s Title','Allows you to change someone else''s title',200,'Diego Andrés','admin@smftricks.com',1,0,1,'https://smftricks.com','ChangeOtherTitle'),
(12,'Change Primary Membergroup','Change your Membergroup!',50000,'Daniel15','dansoft@dansoftaustralia.net',0,1,1,'https://github.com/Daniel15','PrimaryMemberGroup');
/*!40000 ALTER TABLE `smf_stshop_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_subscriptions`
--

DROP TABLE IF EXISTS `smf_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_subscriptions` (
  `id_subscribe` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `cost` text NOT NULL,
  `length` varchar(6) NOT NULL DEFAULT '',
  `id_group` smallint(6) NOT NULL DEFAULT 0,
  `add_groups` varchar(40) NOT NULL DEFAULT '',
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `repeatable` tinyint(4) NOT NULL DEFAULT 0,
  `allow_partial` tinyint(4) NOT NULL DEFAULT 0,
  `reminder` tinyint(4) NOT NULL DEFAULT 0,
  `email_complete` text NOT NULL,
  PRIMARY KEY (`id_subscribe`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_themes`
--

DROP TABLE IF EXISTS `smf_themes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_themes` (
  `id_member` mediumint(9) NOT NULL DEFAULT 0,
  `id_theme` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `variable` varchar(255) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`id_theme`,`id_member`,`variable`(30)),
  KEY `idx_id_member` (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_themes`
--

LOCK TABLES `smf_themes` WRITE;
/*!40000 ALTER TABLE `smf_themes` DISABLE KEYS */;
INSERT INTO `smf_themes` VALUES
(-1,1,'drafts_show_saved_enabled','1'),
(-1,1,'posts_apply_ignore_list','1'),
(-1,1,'return_to_post','1'),
(0,1,'enable_news','1'),
(0,1,'images_url','http://localhost:4000/Themes/default/images'),
(0,1,'name','SMF Default Theme - Curve2'),
(0,1,'newsfader_time','3000'),
(0,1,'number_recent_posts','0'),
(0,1,'show_latest_member','1'),
(0,1,'show_newsfader','0'),
(0,1,'show_stats_index','1'),
(0,1,'theme_dir','/srv/dumb/Themes/default'),
(0,1,'theme_url','http://localhost:4000/Themes/default'),
(0,1,'use_image_buttons','1'),
(0,2,'based_on',''),
(0,2,'based_on_dir','/srv/dumb/Themes/default'),
(0,2,'images_url','http://localhost:4000/Themes/TestTheme/images'),
(0,2,'install_for','2.1 - 2.1.99, 2.1.7'),
(0,2,'name','TestTheme'),
(0,2,'theme_dir','/srv/dumb/Themes/TestTheme'),
(0,2,'theme_layers','html,body'),
(0,2,'theme_templates','index'),
(0,2,'theme_url','http://localhost:4000/Themes/TestTheme'),
(0,2,'version','1.0');
/*!40000 ALTER TABLE `smf_themes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_topics`
--

DROP TABLE IF EXISTS `smf_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_topics` (
  `id_topic` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `is_sticky` tinyint(4) NOT NULL DEFAULT 0,
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_first_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `id_last_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member_started` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_member_updated` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_poll` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_previous_board` smallint(6) NOT NULL DEFAULT 0,
  `id_previous_topic` mediumint(9) NOT NULL DEFAULT 0,
  `num_replies` int(10) unsigned NOT NULL DEFAULT 0,
  `num_views` int(10) unsigned NOT NULL DEFAULT 0,
  `locked` tinyint(4) NOT NULL DEFAULT 0,
  `redirect_expires` int(10) unsigned NOT NULL DEFAULT 0,
  `id_redirect_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `unapproved_posts` smallint(6) NOT NULL DEFAULT 0,
  `approved` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_topic`),
  UNIQUE KEY `idx_last_message` (`id_last_msg`,`id_board`),
  UNIQUE KEY `idx_first_message` (`id_first_msg`,`id_board`),
  UNIQUE KEY `idx_poll` (`id_poll`,`id_topic`),
  KEY `idx_is_sticky` (`is_sticky`),
  KEY `idx_approved` (`approved`),
  KEY `idx_member_started` (`id_member_started`,`id_board`),
  KEY `idx_last_message_sticky` (`id_board`,`is_sticky`,`id_last_msg`),
  KEY `idx_board_news` (`id_board`,`id_first_msg`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_topics`
--

LOCK TABLES `smf_topics` WRITE;
/*!40000 ALTER TABLE `smf_topics` DISABLE KEYS */;
INSERT INTO `smf_topics` VALUES
(1,0,1,1,8,0,3,0,0,0,5,67,0,0,0,0,1);
/*!40000 ALTER TABLE `smf_topics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_user_alerts`
--

DROP TABLE IF EXISTS `smf_user_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_user_alerts` (
  `id_alert` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alert_time` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_member_started` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `content_type` varchar(255) NOT NULL DEFAULT '',
  `content_id` int(10) unsigned NOT NULL DEFAULT 0,
  `content_action` varchar(255) NOT NULL DEFAULT '',
  `is_read` int(10) unsigned NOT NULL DEFAULT 0,
  `extra` text NOT NULL,
  PRIMARY KEY (`id_alert`),
  KEY `idx_id_member` (`id_member`),
  KEY `idx_alert_time` (`alert_time`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_user_alerts_prefs`
--

DROP TABLE IF EXISTS `smf_user_alerts_prefs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_user_alerts_prefs` (
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `alert_pref` varchar(32) NOT NULL DEFAULT '',
  `alert_value` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_member`,`alert_pref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smf_user_alerts_prefs`
--

LOCK TABLES `smf_user_alerts_prefs` WRITE;
/*!40000 ALTER TABLE `smf_user_alerts_prefs` DISABLE KEYS */;
INSERT INTO `smf_user_alerts_prefs` VALUES
(0,'alert_timeout',10),
(0,'announcements',0),
(0,'birthday',2),
(0,'board_notify',1),
(0,'buddy_request',1),
(0,'groupr_approved',3),
(0,'groupr_rejected',3),
(0,'member_group_request',1),
(0,'member_register',0),
(0,'member_report',3),
(0,'member_report_reply',3),
(0,'msg_auto_notify',1),
(0,'msg_like',1),
(0,'msg_mention',1),
(0,'msg_notify_pref',1),
(0,'msg_notify_type',1),
(0,'msg_quote',1),
(0,'msg_receive_body',0),
(0,'msg_report',1),
(0,'msg_report_reply',1),
(0,'pm_new',0),
(0,'pm_notify',1),
(0,'pm_reply',0),
(0,'request_group',1),
(0,'shop_module_steal',1),
(0,'shop_usercredits',1),
(0,'shop_useritems',1),
(0,'shop_usertraded',1),
(0,'topic_notify',1),
(0,'unapproved_attachment',1),
(0,'unapproved_post',1),
(0,'unapproved_reply',3),
(0,'warn_any',0);
/*!40000 ALTER TABLE `smf_user_alerts_prefs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smf_user_drafts`
--

DROP TABLE IF EXISTS `smf_user_drafts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_user_drafts` (
  `id_draft` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_reply` int(10) unsigned NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `poster_time` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `smileys_enabled` tinyint(4) NOT NULL DEFAULT 1,
  `body` mediumtext NOT NULL,
  `icon` varchar(16) NOT NULL DEFAULT 'xx',
  `locked` tinyint(4) NOT NULL DEFAULT 0,
  `is_sticky` tinyint(4) NOT NULL DEFAULT 0,
  `to_list` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_draft`),
  UNIQUE KEY `idx_id_member` (`id_member`,`id_draft`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smf_user_likes`
--

DROP TABLE IF EXISTS `smf_user_likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smf_user_likes` (
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `content_type` char(6) NOT NULL DEFAULT '',
  `content_id` int(10) unsigned NOT NULL DEFAULT 0,
  `like_time` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`content_id`,`content_type`,`id_member`),
  KEY `content` (`content_id`,`content_type`),
  KEY `liker` (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-07  2:12:04
