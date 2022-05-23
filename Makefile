SHELL=/usr/bin/env bash
RED=\033[0;31m
GREEN=\033[0;32m
BOLD=\033[1m
NC=\033[0m
COMPOSE_CMD:=docker-compose -f .docker/docker-compose.yml --env-file .env
PROJECT_NAME=lemp

.PHONY: help
help:
	@echo
	@echo " Usage: make [target] ..."
	@echo " Targets:"
	@echo " up ................... Starts all containers and initializes the project on the first run."
	@echo " down ................. Shuts down all containers. This does not cause any data loss."
	@echo " wipe_db .............. Deletes all DB content and users. New DB will be created on the next run with new root passwords."
	@echo " clean ................ Deletes the DB (see wipe_db), resets env-variables and wipes the app's vendor folder."
	@echo " composer ............. An alias to run the composer CLI in this project (e.g. try:  'make composer update' )."
	@echo " php .................. Allows access to the PHP CLI for this project. (e.g. try:  'make php -a' )."
	@echo " dc, docker-compose ... Wrapper for docker compose."
	@echo

.PHONY: up
up:
	@# Copy .env file
	@test -f .env || (cp .env.example .env && echo -e '$(GREEN)$(BOLD)'"Copied .env.example to .env"'$(NC)'"\n")

	@# Start all containers and pipe output to current shell
	@# If the DB containers are run for the first time, it will also save DB root passwords into the .env file
	@stdbuf -oL $(COMPOSE_CMD) up | \
	tee /dev/tty | \
	grep --line-buffered "GENERATED ROOT PASSWORD" | \
	sed -u -r 's/\|.*\:\ //' | \
	sed -u -r 's/.*mysql\  */MYSQL_ROOT_PASSWORD=/' | \
	sed -u -r 's/.*mariadb\  */MARIADB_ROOT_PASSWORD=/' >> .env

.PHONY: down
down:
	@$(COMPOSE_CMD) down


.PHONY: wipe_db
wipe_db: down
	@read -p "This will wipe your local databases. Continue? [y/N] " -n 1 REPLY ;\
	echo "" ;\
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		docker volume rm $(PROJECT_NAME)_mariadb-persistence && docker volume create $(PROJECT_NAME)_mariadb-persistence ;\
		docker volume rm $(PROJECT_NAME)_mysql-persistence && docker volume create $(PROJECT_NAME)_mysql-persistence ;\
		docker volume rm $(PROJECT_NAME)_postgres-persistence && docker volume create $(PROJECT_NAME)_postgres-persistence ;\
		echo "Your databases have been wiped." ;\
		\
		test -f .env \
		&& sed -i.tmp '/[MYSQL|MARIADB]_ROOT_PASSWORD/d' ./.env \
		&& rm .env.tmp \
		&& echo "The db root-passwords has been removed from your .env file." \
		|| true ;\
	else \
		echo "Operation canceled." ;\
		exit 1 ;\
	fi

.PHONY: clean
clean: wipe_db
	@$(COMPOSE_CMD) down -v --remove-orphans
	@test -f .env && mv .env .env.bak && echo "Your .env file has been deleted (backed-up as .env.bak)." || true
	@test -d src/vendor && rm -fr src/vendor && echo "Your /vendor folder has been deleted." || true
	@echo -e '$(GREEN)$(BOLD)'"Everything is clean now."'$(NC)'

.PHONY: composer
composer:
	@$(COMPOSE_CMD) run --rm composer $(ARGS)


.PHONY: php
php:
	@$(COMPOSE_CMD) run --workdir="/code" --rm php-fpm php $(ARGS)


.PHONY: docker-compose dc
docker-compose dc:
	$(COMPOSE_CMD) $(ARGS)

.PHONY: pgcli pg psql
pgcli pg psql:
	@export $$(cat .env | grep POSTGRES_ | xargs) ;\
	export PGHOST=localhost PGPORT=$$POSTGRES_PORT PGUSER=$$POSTGRES_USER PGPASSWORD=$$POSTGRES_PASSWORD PGDATABASE=$$POSTGRES_DATABASE ;\
	if ! hash pgcli 2>/dev/null; then \
		if ! hash psql 2>/dev/null; then \
            echo -e "neither $(RED)pgcli$(NC) nor $(RED)psql$(NC) are installed; please install at least one of them!" ;\
            exit 1 ;\
		fi ;\
		echo -e "$(RED)pgcli$(NC) command line utility not found; falling back to $(RED)psql$(NC)..." ;\
		psql ;\
	else \
		pgcli ;\
	fi
