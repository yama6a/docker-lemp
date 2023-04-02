-- noinspection SqlNoDataSourceInspectionForFile

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


INSERT INTO `animals` (`id`, `species`, `name`, `color`, `has_fur`) VALUES
(uuid(), 'feline', 'bobcat', 'beige-ish?', 1),
(uuid(), 'feline', 'panther', 'black', 1),
(uuid(), 'canine', 'fox', 'orange', 1),
(uuid(), 'canine', 'chihuahua', 'brown', 1),
(uuid(), 'reptilian', 'king cobra', 'olive green', 0),
(uuid(), 'amphibian', 'poison dart frog', 'red', 0);


INSERT INTO animal_customer (animal_id, customer_id, count)
SELECT animals.id, uuid(), floor(rand() * 10 + 1)
FROM animals
ORDER BY rand()
LIMIT 6;


COMMIT;

