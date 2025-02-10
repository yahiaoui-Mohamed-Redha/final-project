-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 03, 2025 at 04:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project`
--

-- --------------------------------------------------------

--
-- Table structure for table `epost`
--

CREATE TABLE `epost` (
  `postal_code` int(11) NOT NULL,
  `etablissement_name` varchar(100) NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `wilaya_num` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `epost`
--

INSERT INTO `epost` (`postal_code`, `etablissement_name`, `region`, `address`, `wilaya_num`) VALUES
(35000, 'BOUMERDES-RP', '', 'CITE 408 LOGEMENTS BOUMERDES', 35),
(35001, 'BORDJ-MENAIEL', '', 'RUE BENNOUR ALI BORDJ MENAIEL', 35),
(35002, 'BORDJ-MENAIEL-AMIROUCHE', '', 'BD COLONEL AMIROUCHE BORDJ MENAIEL', 35),
(35003, 'BOUDOUAOU', '', 'RUE 1 ER NOVEMBRE BOUDOUAOU', 35),
(35004, 'DELLYS', '', 'RUE MAABOUT DELLYS', 35),
(35005, 'THENIA', '', 'RUE REBATCHI THENIA', 35),
(35006, 'BENI-AMRANE', '', 'RUE CHAALAL SAID BENI AMRANE', 35),
(35007, 'BOUMERDES-FRANTZ-FANON', '', 'CITE 350 LOGEMENTS BOUMERDES', 35),
(35008, 'CHABET-EL-AMEUR', '', 'RUE DOUDAH ALI CHABET EL AMEUR', 35),
(35009, 'ISSER', '', 'BD COLONEL AMIROUCHE ISSER', 35),
(35010, 'KHEMIS-EL-KHECHNA', '', 'RUE BOUZIANE HOCINE K. E. K.', 35),
(35011, 'OULED-MOUSSA', '', 'RUE ABDELAZIZ EL KEBIR OULED MOUSSA', 35),
(35012, 'ZEMMOURI', '', 'RUE BELHOUCHET HAMOUD ZEMMOURI', 35),
(35013, 'BAGHLIA', '', '25 RUE KACIMI MOHAMED BAGHLIA', 35),
(35014, 'CORSO', '', 'RUE TADJOUIMAT LOUNES CORSO', 35),
(35015, 'HAMMADI', '', 'RUE KORBABE AEK HAMMEDI', 35),
(35016, 'ISSER-VILLE', '', 'RUE MELLAH ALI ISSER-VILLE', 35),
(35017, 'LARBATACHE', '', 'RUE BOUCHKIR MED LARBATACHE', 35),
(35018, 'NACIRIA', '', 'RUE BENNOUR ALI NACIRIA', 35),
(35019, 'SIDI-DAOUD', '', 'RUE GUELMI M\'HAMED SIDI DAOUD', 35),
(35020, 'SOUK-EL-HAD', '', 'RUE TIMEZRIT SOUK EL HAD', 35),
(35021, 'TIDJELABINE', '', 'RUE COLONEL AMIROUCHE TIDJELABINE', 35),
(35022, 'AFIR', '', 'RUE GOURMI MED SAID AFIR', 35),
(35023, 'BOUDOUAOU-BENTERQUIA', '', 'CITE 850 LOGEMENTS NOUVELLE VILLE BOUDOUAOU PLATEAU', 35),
(35024, 'DJINET', '', 'RUE PRINCIPALE DJINET', 35),
(35025, 'KOUDIAT-EL-ARAIS', '', 'CENTRE KOUDIET LAREIS', 35),
(35026, 'LEGHATA', '', 'RUE MOHAMED AMMARI LEGHATA', 35),
(35027, 'ROUAFA', '', 'CENTRE ROUAFA', 35),
(35028, 'SI-MUSTAPHA', '', 'RUE M\'HAMED BEN AMROUCHE SI MUSTAPHA', 35),
(35029, 'TAOURGA', '', 'RUE MAHIEDINE MED SEGHIR TAOUERGA', 35),
(35030, 'ABADA', '', 'VILLAGE ABADA - ABADA', 35),
(35031, 'AMMAL', '', 'RUE CHAALAL ALI AMMAL', 35),
(35032, 'AZROU', '', 'VILLAGE AZROU - AZROU', 35),
(35033, 'BENCHOUD', '', 'VILLAGE AGRICOLE BEN CHOUD', 35),
(35034, 'BENMERZOUGA', '', 'VILLAGE BEN MERZOUGA', 35),
(35035, 'BOUAIDEL', '', 'VILLAGE BOUAIDEL', 35),
(35036, 'BOUDHAR', '', 'VILLAGE BOUDHAR', 35),
(35037, 'BOUDOUAOU-EL-BAHRI', '', 'RUE TAABEST BOUDOUAOU EL BAHRI', 35),
(35038, 'BOUZEGZA-KEDDARA', '', 'VILLAGE KEDDARA', 35),
(35039, 'CHEBACHEB', '', 'RUE PRINCIPALE CHEBACHEB', 35),
(35040, 'CHLEF-BOUMRAOU', '', 'VILLAGE AGRICOLE CHLEF BOUMERAOU', 35),
(35041, 'FOURAR-AIN-SEBAOU', '', 'VILLAGE AGRICOLE FOURAR AIN SEBAOU', 35),
(35042, 'HADDADA', '', 'VILLAGE HADADA', 35),
(35043, 'HADJ-AHMED', '', 'VILLAGE AGRICOLE HADJ AHMED', 35),
(35044, 'HAOUCH-BEN-OUALI', '', 'VILLAGE HAOUCH-BEN-OUALI', 35),
(35045, 'HAOUCH-EL-MOKHFI', '', 'HAI HAOUCH EL MOKHFI - OULED HEDADJ', 35),
(35046, 'KARMA', '', 'VILLAGE EL KARMA', 35),
(35047, 'KEHF-EL-GHARBI', '', 'VILLAGE KEF EL GHARBI', 35),
(35048, 'KHAROUBA', '', 'CITE KALACHE BRAHIM EL KHAROUBA', 35),
(35049, 'KHEMIS-EL-KHECHNA 17 JUIN', '', 'VILLAGE AGRICOLE K. E. K. 17 JUIN', 35),
(35050, 'OULED-AISSA', '', 'CENTRE OULED AISSA - OULED AISSA', 35),
(35051, 'OULED-ALI', '', 'VILLAGE AGRICOLE OULED-ALI', 35),
(35052, 'OULED-HADADJ', '', 'CITE GARIDI RABAH OULED HEDADJ', 35),
(35053, 'OULED-H\'MIDA', '', 'VILLAGE OULED H\'MIDA', 35),
(35054, 'OURIACHA', '', 'VILLAGE OULED H\'MIDA', 35),
(35055, 'SEBAOU-EL-KEDIM', '', 'VILLAGE SEBAOU EL KEDIM', 35),
(35056, 'SIDI-EL-MEDJNI', '', '16 CITE BELKACEM GHERNAOUT SIDI EL MEDJNI DELLYS', 35),
(35057, 'TAKDEMPT', '', 'ROUTE NATIONALE 24 TAKDEMPT', 35),
(35058, 'TCHOUCHFI-BOUBERAK', '', 'VILLAGE TCHOUCHFI BOUBERRRAK', 35),
(35059, 'TIZI-NALI-SLIMANE', '', 'VILLAGE TIZI N\'ALI SLIMANE', 35),
(35060, 'ZAATRA', '', 'VILLAGE ZAATRA', 35),
(35061, 'ZEMMOURI-EL-BAHRI', '', 'VILLAGE ZEMMOURI EL BAHRI', 35),
(35062, 'BOUMERDES LE ROCHER', '', 'CITE 1200 LOGEMENTS COMPLEXE DES PTT BOUMERDES', 35),
(35063, 'BOUMERDES-FACULTE-DES-SCIENCES', '', 'FACULTE DES SCIENCES. ROUTE DE LA GARE FERROVIAIRE - BOUMERDES', 35),
(35064, 'BOUDOUAOU-FACULTE-DE-DROIT', '', 'FACULTE DE DROIT, BOUDOUAOU, BOUMERDES', 35),
(35065, 'BOUDOUAOU-PLATEAU', '', 'PLATEAU \"EST\"\" BOUDOUAOU EL BAHRI\"', 35),
(35066, 'ISSER-EPJGN', '', 'ESGN RUE ABANE REMDANE ISSER', 35),
(35067, 'ZEMMOURI-AADL', NULL, 'CITEE AADL 919 LOGEMENTS, BLOC 12B N° 38 Ã, B, C, ZEMMOURI', 35);

-- --------------------------------------------------------

--
-- Table structure for table `ordermission`
--

CREATE TABLE `ordermission` (
  `order_num` int(11) NOT NULL,
  `direction` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `motif` text NOT NULL,
  `moyen_tr` varchar(50) NOT NULL,
  `date_depart` date NOT NULL,
  `date_retour` date NOT NULL,
  `archived` tinyint(1) DEFAULT 0,
  `technicien_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `panne`
--

CREATE TABLE `panne` (
  `panne_num` int(11) NOT NULL,
  `panne_name` varchar(100) NOT NULL,
  `date_signalement` date NOT NULL,
  `description` text DEFAULT NULL,
  `panne_etat` enum('nouveau','en_cours','résolu','fermé') DEFAULT 'nouveau',
  `archived` tinyint(1) DEFAULT 0,
  `type_id` int(11) NOT NULL,
  `receveur_id` int(11) NOT NULL,
  `rap_num` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `panne`
--

INSERT INTO `panne` (`panne_num`, `panne_name`, `date_signalement`, `description`, `panne_etat`, `archived`, `type_id`, `receveur_id`, `rap_num`) VALUES
(1, 'in the carte resuo', '2025-01-12', 'bfgdfg', 'nouveau', 0, 3, 2, NULL),
(2, 'in the power ', '2025-01-12', 'gfgfdgdfgdfg', 'nouveau', 0, 1, 2, NULL),
(3, 'in the carte resuo', '2025-01-18', 'rterte', 'nouveau', 0, 3, 2, 8),
(4, 'in the carte grafik', '2025-01-18', 'terte', 'nouveau', 0, 1, 2, 8),
(5, 'عطل في أجهزة الكهرباء', '2025-01-30', 'عطل في أجهزة الكهرباء', 'nouveau', 0, 1, 7, 9),
(6, 'in the carte grafik', '2025-01-30', 'عطل في أجهزة الكهرباء', 'nouveau', 0, 1, 7, 9),
(7, 'عطل', '2025-01-30', 'vxcvcxvxxcvxc', 'nouveau', 0, 3, 3, 10);

-- --------------------------------------------------------

--
-- Table structure for table `propos_type`
--

CREATE TABLE `propos_type` (
  `propos_id` int(11) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `receveur_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `propos_type`
--

INSERT INTO `propos_type` (`propos_id`, `type_name`, `description`, `status`, `created_at`, `receveur_id`) VALUES
(1, 'reseau', 'reseau cable switches... etc', 'approved', '2025-01-11 22:06:18', 2);

-- --------------------------------------------------------

--
-- Table structure for table `rapport`
--

CREATE TABLE `rapport` (
  `rap_num` int(11) NOT NULL,
  `rap_name` varchar(100) NOT NULL,
  `rap_date` date NOT NULL,
  `expediteur` varchar(100) DEFAULT NULL,
  `archived` tinyint(1) DEFAULT 0,
  `panne_num` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rapport`
--

INSERT INTO `rapport` (`rap_num`, `rap_name`, `rap_date`, `expediteur`, `archived`, `panne_num`, `user_id`, `description`) VALUES
(6, 'تقرير الأعطال - 2025-01-12 15:01:12', '2025-01-12', NULL, 0, NULL, 2, 'تم إنشاء التقرير تلقائيًا.'),
(7, 'تقرير الأعطال - 2025-01-12 15:05:03', '2025-01-12', NULL, 0, NULL, 2, 'تم إنشاء التقرير تلقائيًا.'),
(8, 'تقرير الأعطال 2025-01-18 19:22:12', '2025-01-18', NULL, 0, NULL, 2, 'تقرير تم إنشاؤه تلقائيًا مع الأعطال'),
(9, 'تقرير الأعطال 2025-01-30 15:27:38', '2025-01-30', NULL, 0, NULL, 7, 'تقرير تم إنشاؤه تلقائيًا مع الأعطال'),
(10, 'تقرير الأعطال 2025-01-30 15:33:26', '2025-01-30', NULL, 0, NULL, 3, 'تقرير تم إنشاؤه تلقائيًا مع الأعطال');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_nom` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_nom`) VALUES
(1, 'admin'),
(2, 'technicien'),
(3, 'receveur');

-- --------------------------------------------------------

--
-- Table structure for table `ticket`
--

CREATE TABLE `ticket` (
  `ticket_num` int(11) NOT NULL,
  `panne_num` int(11) NOT NULL,
  `rap_num` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket`
--

INSERT INTO `ticket` (`ticket_num`, `panne_num`, `rap_num`) VALUES
(1, 1, 6),
(2, 2, 7);

-- --------------------------------------------------------

--
-- Table structure for table `type_panne`
--

CREATE TABLE `type_panne` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `receveur_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `type_panne`
--

INSERT INTO `type_panne` (`type_id`, `type_name`, `description`, `receveur_id`) VALUES
(1, 'hardware ', 'this type for hardware ', 3),
(3, 'reseau', 'reseau cable switches... etc', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_mobile` varchar(15) DEFAULT NULL,
  `user_fixe` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `etat_compte` tinyint(1) DEFAULT 1,
  `role_id` int(11) NOT NULL,
  `grade` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `nom`, `prenom`, `email`, `user_mobile`, `user_fixe`, `password`, `etat_compte`, `role_id`, `grade`, `postal_code`) VALUES
(1, 'technicien', 'Technicien', 'User ', 'technicien@gmail.com', '1234567890', '1234567890', '482c811da5d5b4bc6d497ffa98491e38', 1, 2, NULL, NULL),
(2, 'receveuruser', 'Receveur', 'User ', 'receveur@gmail.com', '1234567890', '1234567890', '482c811da5d5b4bc6d497ffa98491e38', 1, 3, NULL, NULL),
(3, 'admin123', 'admin', 'User ', 'admin@gmail.com', '1234567890', '1234567890', '482c811da5d5b4bc6d497ffa98491e38', 1, 1, NULL, NULL),
(4, 'mohamed_redha', 'YAHIAOUI', 'Mohamed Redha', 'mohamed@gmail.com', '0560590585', '024728265', '123456789', 1, 1, NULL, NULL),
(5, 'zaki_amrouch', 'zaki', 'amrouch', 'zaki@gmail.com', '', '', '083ed1c0dba74db076c33bdbcfac54f6', 1, 3, NULL, NULL),
(6, 'zaki_amrouch1', 'zaki', 'amrouch', '', '', '', '4dc0ef8a6d8a1754b6a9cff70710dfae', 1, 2, NULL, NULL),
(7, 'yahiaoui_mohamed', 'Yahiaoui', 'Mohamed', 'rs@ou.com', '', '', '25f9e794323b453885f5181f1b624d0b', 1, 3, NULL, NULL),
(9, 'yahiaoui_mohamed1', 'Yahiaoui', 'Mohamed', 'ad@min.com', '', '', '$2y$10$L28V23WRv7bd2SmXfkTBNOLa.cRZhHZ3fmh.7h67oxcBzeyHNIpEa', 1, 1, 'Sous Directeur Informatique', NULL),
(11, 'yahiaoui_mohamed', 'Yahiaoui', 'Mohamed', 'mohamedredayah@gmail.com', '', '', '$2y$10$MzdcpueSDiOBYNm9f/iRT.hpo0RKw6ZZ26OfZgQDFZaQGfGEwbjg2', 1, 1, 'Sous Directeur Informatique', NULL),
(12, 'yahiaoui_mohamed2', 'Yahiaoui', 'Mohamed', NULL, '', '', '$2y$10$6q5gy11n6KPhsu9z6mp65eMxfnkvGifVAUtUHHECfEYVTpWwDq9FW', 1, 1, 'Sous Directeur Informatique', NULL),
(13, 'djaffer_djaffer', 'DJAFFER', 'DJAFFER', NULL, '', '', '$2y$10$W0UdjEngZzxWbGbDu6vOtuxbFXJzmpnYQW8oxQ2jHv/x0aCxr/xwq', 1, 2, 'TS', NULL),
(14, 'hamide_hamide', 'hamide', 'hamide', NULL, '', '', '$2y$10$PI1R22MLvsJhNwXH3xVCjuky/VbMeVtuHl.Z.YJZC/OSBwxGavX3y', 1, 3, 'Receveur', '35017');

-- --------------------------------------------------------

--
-- Table structure for table `wilaya`
--

CREATE TABLE `wilaya` (
  `wilaya_num` int(11) NOT NULL,
  `wilaya_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wilaya`
--

INSERT INTO `wilaya` (`wilaya_num`, `wilaya_name`) VALUES
(35, 'BOUMERDES');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `epost`
--
ALTER TABLE `epost`
  ADD PRIMARY KEY (`postal_code`),
  ADD KEY `wilaya_num` (`wilaya_num`);

--
-- Indexes for table `ordermission`
--
ALTER TABLE `ordermission`
  ADD PRIMARY KEY (`order_num`),
  ADD KEY `technicien_id` (`technicien_id`);

--
-- Indexes for table `panne`
--
ALTER TABLE `panne`
  ADD PRIMARY KEY (`panne_num`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `receveur_id` (`receveur_id`),
  ADD KEY `fk_panne_rapport` (`rap_num`);

--
-- Indexes for table `propos_type`
--
ALTER TABLE `propos_type`
  ADD PRIMARY KEY (`propos_id`),
  ADD KEY `fk_receveur` (`receveur_id`);

--
-- Indexes for table `rapport`
--
ALTER TABLE `rapport`
  ADD PRIMARY KEY (`rap_num`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `panne_num` (`panne_num`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `ticket`
--
ALTER TABLE `ticket`
  ADD PRIMARY KEY (`ticket_num`),
  ADD KEY `panne_num` (`panne_num`),
  ADD KEY `rap_num` (`rap_num`);

--
-- Indexes for table `type_panne`
--
ALTER TABLE `type_panne`
  ADD PRIMARY KEY (`type_id`),
  ADD KEY `receveur_id` (`receveur_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `postal_code` (`postal_code`);

--
-- Indexes for table `wilaya`
--
ALTER TABLE `wilaya`
  ADD PRIMARY KEY (`wilaya_num`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `epost`
--
ALTER TABLE `epost`
  MODIFY `postal_code` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35068;

--
-- AUTO_INCREMENT for table `ordermission`
--
ALTER TABLE `ordermission`
  MODIFY `order_num` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `panne`
--
ALTER TABLE `panne`
  MODIFY `panne_num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `propos_type`
--
ALTER TABLE `propos_type`
  MODIFY `propos_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rapport`
--
ALTER TABLE `rapport`
  MODIFY `rap_num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ticket`
--
ALTER TABLE `ticket`
  MODIFY `ticket_num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `type_panne`
--
ALTER TABLE `type_panne`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `wilaya`
--
ALTER TABLE `wilaya`
  MODIFY `wilaya_num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `epost`
--
ALTER TABLE `epost`
  ADD CONSTRAINT `epost_ibfk_1` FOREIGN KEY (`wilaya_num`) REFERENCES `wilaya` (`wilaya_num`);

--
-- Constraints for table `ordermission`
--
ALTER TABLE `ordermission`
  ADD CONSTRAINT `ordermission_ibfk_1` FOREIGN KEY (`technicien_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `panne`
--
ALTER TABLE `panne`
  ADD CONSTRAINT `fk_panne_rapport` FOREIGN KEY (`rap_num`) REFERENCES `rapport` (`rap_num`),
  ADD CONSTRAINT `panne_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `type_panne` (`type_id`),
  ADD CONSTRAINT `panne_ibfk_2` FOREIGN KEY (`receveur_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `propos_type`
--
ALTER TABLE `propos_type`
  ADD CONSTRAINT `fk_receveur` FOREIGN KEY (`receveur_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `rapport`
--
ALTER TABLE `rapport`
  ADD CONSTRAINT `rapport_ibfk_1` FOREIGN KEY (`panne_num`) REFERENCES `panne` (`panne_num`),
  ADD CONSTRAINT `rapport_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `ticket_ibfk_1` FOREIGN KEY (`panne_num`) REFERENCES `panne` (`panne_num`),
  ADD CONSTRAINT `ticket_ibfk_2` FOREIGN KEY (`rap_num`) REFERENCES `rapport` (`rap_num`);

--
-- Constraints for table `type_panne`
--
ALTER TABLE `type_panne`
  ADD CONSTRAINT `type_panne_ibfk_1` FOREIGN KEY (`receveur_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
