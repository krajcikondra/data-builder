CREATE TABLE `address` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `street` varchar(50) NOT NULL,
  `home_number` varchar(20) DEFAULT NULL COMMENT 'Číslo popisné',
  `city` varchar(40) NOT NULL,
  `zip_code` varchar(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci;
