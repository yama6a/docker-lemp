##ToDos for this project


### Done
- [x] Add Makefile to improve DX
- [x] Use the same PMA instance for both MariaDB and MySQL instead of one PMA respectively
- [x] Use PMA 5-fpm-alpine image and reuse existing nginx container instead of including Apache in PMA-container

### ToDo
- [ ] Reduce MySQL container mem footprint (360MB vs MariaDB's 50MB)
  - https://github.com/alexanderkoller/low-memory-mysql/blob/master/low-memory-my.cnf
  - https://stackoverflow.com/questions/60244889/how-to-decrease-mysql-container-memory-usage
- [ ] Add Postgres support
- [ ] Turn into Cookiecutter template
   1. Allow choosing DB
   2. Allow choosing PHP version
- [ ] Add linters as github-workflows
- [ ] Decide on infrastructure setup and IAC technology
