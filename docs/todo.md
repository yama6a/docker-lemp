##ToDos for this project


### Done
- [x] Add Makefile to improve DX
- [x] Use the same PMA instance for both MariaDB and MySQL instead of one PMA respectively
- [x] Use PMA 5-fpm-alpine image and reuse existing nginx container instead of including Apache in PMA-container
- [x] Reduce MySQL container idle mem footprint (360MB --> 80MB)

### ToDo
- [ ] Use docker volume instead of FS mount for DB persistence
- [ ] Add Postgres support
- [ ] Turn into Cookiecutter template
   1. Allow choosing DB
   2. Allow choosing PHP version
- [ ] Add linters as github-workflows
- [ ] Decide on infrastructure setup and IAC technology
