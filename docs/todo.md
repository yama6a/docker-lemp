##ToDos for this project


#### Containerization
1. Use PMA 5-fpm-alpine image and reuse existing nginx container to reduce mem footprint of this project
2. Use the same PMA instance for both MariaDB and MySQL by adding custom config for credentials:
   - https://hub.docker.com/r/phpmyadmin/phpmyadmin/
3. Add Postgres support
4. Turn into Cookiecutter template
   1. Allow choosing DB
   2. Allow choosing PHP version
5. Add linters as github-workflows
6. Decide on infrastructure setup and IAC technology
