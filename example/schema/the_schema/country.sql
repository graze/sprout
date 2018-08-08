CREATE TABLE `country` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_code` char(2) NOT NULL,
  `country_code_iso2` char(2) NOT NULL,
  `country_code_iso3` char(3) NOT NULL,
  `name` varchar(100) NOT NULL,
  `decimal_point` varchar(2) NOT NULL,
  `thousands_separator` varchar(2) NOT NULL,
  `added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `country_code` (`country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

