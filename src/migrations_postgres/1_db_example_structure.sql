CREATE EXTENSION IF NOT EXISTS pgcrypto;


CREATE TABLE IF NOT EXISTS animals (
  id         UUID     PRIMARY KEY  DEFAULT gen_random_uuid(),
  species    text     NOT NULL     CONSTRAINT species_cannot_be_empty NOT NULL CHECK (species <> ''),
  name       text     NOT NULL     CONSTRAINT name_cannot_be_empty NOT NULL CHECK (name <> ''),
  color      text     NOT NULL     CONSTRAINT color_cannot_be_empty NOT NULL CHECK (color <> ''),
  "has_fur"  boolean  NOT NULL
);


CREATE TABLE IF NOT EXISTS customers (

  id          UUID  PRIMARY KEY    DEFAULT gen_random_uuid(),
  phone       text  DEFAULT NULL,
  first_name  text  NOT NULL       CONSTRAINT first_name_cannot_be_empty NOT NULL CHECK (first_name <> ''),
  last_name   text  NOT NULL       CONSTRAINT last_name_cannot_be_empty NOT NULL CHECK (last_name <> '')
);

CREATE TABLE IF NOT EXISTS animal_customer (
   animal_id    UUID     NOT NULL,
                CONSTRAINT animal_customer_animal_id FOREIGN KEY ("animal_id") REFERENCES "animals" ("id"),
   customer_id  UUID     NOT NULL,
                CONSTRAINT animal_customer_customer_id FOREIGN KEY ("customer_id") REFERENCES "customers" ("id"),
   count        INTEGER  NOT NULL DEFAULT '1',
   PRIMARY KEY ("animal_id","customer_id")
);
