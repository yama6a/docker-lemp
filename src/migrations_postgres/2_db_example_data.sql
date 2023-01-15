
INSERT INTO customers (phone, first_name, last_name)
VALUES
   ('+46 - 72 886 1234','Dwayne','Johnson'),
   ('+1 - 555 1234','John','Cena'),
   ('+30 - 443 1122','Steve','Austin'),
   ('+555 - 998 1222','Rey','Mysterio');

INSERT INTO animals (species, name, color, has_fur)
VALUES
       ('feline','bobcat','beige-ish?',true),
       ('feline','panther','black',true),
       ('canine','fox','orange',true),
       ('canine','chihuahua','brown',true),
       ('reptilian','king cobra','olive green',false),
       ('amphibian','poison dart frog','red',false);

INSERT INTO animal_customer (animal_id, customer_id, count)
    SELECT animals.id, customers.id, floor(random() * 10 + 1)::int
    FROM animals CROSS JOIN customers
    ORDER BY random()
    LIMIT 6;
