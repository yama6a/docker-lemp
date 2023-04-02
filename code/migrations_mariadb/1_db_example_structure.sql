-- noinspection SqlNoDataSourceInspectionForFile

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `customers` (
  `id` varchar(36) NOT NULL, -- for sake of the demo's simplicity, we use a string instead of binary(16) with uuid_to_bin() shenanigans (mariadb 10.7+ has UUID datatype but RDS only supports up to 10.6)
  `phone` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


COMMIT;

