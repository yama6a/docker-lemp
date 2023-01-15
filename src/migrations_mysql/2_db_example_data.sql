-- noinspection SqlNoDataSourceInspectionForFile

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


INSERT INTO `animals` (`id`, `species`, `name`, `color`, `has_fur`) VALUES
(1, 'feline', 'bobcat', 'beige-ish?', 1),
(2, 'feline', 'panther', 'black', 1),
(3, 'canine', 'fox', 'orange', 1),
(4, 'canine', 'chihuahua', 'brown', 1),
(5, 'reptilian', 'king cobra', 'olive green', 0),
(6, 'amphibian', 'poison dart frog', 'red', 0);

INSERT INTO `customers` (`id`, `phone`, `first_name`, `last_name`) VALUES
(1, '+46 - 72 886 1234', 'Dwayne', 'Johnson'),
(2, '+1 - 555 1234', 'John', 'Cena'),
(3, '+30 - 443 1122', 'Steve', 'Austin'),
(4, '+555 - 998 1222', 'Rey', 'Mysterio');

INSERT INTO `animal_customer` (`animal_id`, `customer_id`, `count`) VALUES
(6, 2, 1),
(6, 3, 15),
(3, 4, 4),
(4, 4, 2),
(5, 1, 1),
(2, 1, 2);

COMMIT;

