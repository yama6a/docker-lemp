##ToDos for this project


### Done
- [x] Add Makefile to improve DX

### ToDo
- [ ] Use PMA 5-fpm-alpine image and reuse existing nginx container to reduce mem footprint of this project
- [ ] Use the same PMA instance for both MariaDB and MySQL by adding custom config for credentials:
   - https://hub.docker.com/r/phpmyadmin/phpmyadmin/
- [ ] Add Postgres support
- [ ] Turn into Cookiecutter template
   1. Allow choosing DB
   2. Allow choosing PHP version
- [ ] Add linters as github-workflows
- [ ] Decide on infrastructure setup and IAC technology
