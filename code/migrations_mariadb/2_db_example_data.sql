-- noinspection SqlNoDataSourceInspectionForFile

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

INSERT INTO `customers` (`id`, `phone`, `first_name`, `last_name`) VALUES
(uuid(), '+46 - 72 886 1234', 'Dwayne', 'Johnson'),
(uuid(), '+1 - 555 1234', 'John', 'Cena'),
(uuid(), '+30 - 443 1122', 'Steve', 'Austin'),
(uuid(), '+555 - 998 1222', 'Rey', 'Mysterio');
COMMIT;

