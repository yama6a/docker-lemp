##ToDos for this project


### Done
- [x] Add Makefile to improve DX
- [x] Use the same PMA instance for both MariaDB and MySQL instead of one PMA respectively
- [x] Use PMA 5-fpm-alpine image and reuse existing nginx container instead of including Apache in PMA-container
- [x] Reduce MySQL container idle mem footprint (360MB --> 80MB)
- [x] Use docker volume instead of FS mount for DB persistence
- [x] Allow composer to have persistent cache and user config

### ToDo
- [ ] Ensure composer container can access private repos
  - https://forums.docker.com/t/ssh-agent-forwarding-into-docker-compose-environment-is-not-working/93320
  - https://hub.docker.com/_/composer
- [ ] Add Postgres support
- [ ] Turn into Cookiecutter template
   1. Allow choosing DB
   2. Allow choosing PHP version
- [ ] Add linters as github-workflows
- [ ] Decide on infrastructure setup and IAC technology
