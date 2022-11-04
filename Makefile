SHELL=/usr/bin/env bash
RED=\033[0;31m
GREEN=\033[0;32m
YELLOW=\033[1;33m
BOLD=\033[1m
NC=\033[0m
COMPOSE_CMD:=docker-compose -f .docker/docker-compose.yml --env-file .env
PROJECT_NAME=my-awesome-project
VERSION?=local

.PHONY: help
help: ## Display this help.
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z_0-9-]+:.*?##/ { printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

.PHONY: up
up: ## Starts all containers and inimake celtializes the project on the first run.
	@# Copy .env file
	@test -f .env || (cp .env.example .env && echo -e '$(GREEN)$(BOLD)'"Copied .env.example to .env"'$(NC)'"\n" && sed -i '' -r -e 's/COMPOSE_PROJECT_NAME=".*"/COMPOSE_PROJECT_NAME="$(PROJECT_NAME)"/' .env)

	@# Start all containers and pipe output to current shell
	@# If the DB containers are run for the first time, it will also save DB root passwords into the .env file
	@stdbuf -oL $(COMPOSE_CMD) up | \
	tee /dev/tty | \
	grep --line-buffered "GENERATED ROOT PASSWORD" | \
	sed -u -r 's/\|.*\:\ //' | \
	sed -u -r 's/.*mysql\  */ROOT_PASSWORD_MYSQL=/' | \
	sed -u -r 's/.*mariadb\  */ROOT_PASSWORD_MARIADB=/' >> .env

.PHONY: down
down: ## Shuts down all containers. This does not cause any data loss.
	@$(COMPOSE_CMD) down


.PHONY: wipe_db
wipe_db: down ## Deletes all DB content and users. New DB will be created on the next run with new root passwords.
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
clean: wipe_db ## Deletes the DB (see wipe_db), resets env-variables and wipes the app's vendor folder.
	@$(COMPOSE_CMD) down -v --remove-orphans
	@test -f .env && mv .env .env.bak && echo "Your .env file has been deleted (backed-up as .env.bak)." || true
	@test -d src/vendor && rm -fr src/vendor && echo "Your /vendor folder has been deleted." || true
	@echo -e '$(GREEN)$(BOLD)'"Everything is clean now."'$(NC)'

.PHONY: build
build: ## Builds the release-images for php-fpm and nginx
	docker build -f .docker/php-fpm/Dockerfile -t $(PROJECT_NAME)-php:$(VERSION) --build-arg IMAGE=deploy .
	docker build -f .docker/nginx/Dockerfile -t $(PROJECT_NAME)-nginx:$(VERSION) --build-arg IMAGE=deploy .
	# ToDo: in k8s pod manifest, add host entry for php-fpm -> 127.0.0.1 (php-fpm is referenced in nginx' default.conf)

#########################################
####   Development CLI tools below   ####
#########################################
.PHONY: php
php: ## Allows access to the PHP CLI for this project. (e.g. try:  'make php -a' ).
	@$(COMPOSE_CMD) run --workdir="/code" --rm php-fpm php $(ARGS)


.PHONY: dc
dc: ## Wrapper for docker-compose.
	$(COMPOSE_CMD) $(ARGS)

.PHONY: composer
composer: ## An alias to run the composer CLI in this project (e.g. try:  'make composer update' ).
	@$(COMPOSE_CMD) run --rm composer $(ARGS)

.PHONY: pgcli
pgcli: # Runs pgcli in a container and connects to the postgres development DB
	export $$(cat .env | grep POSTGRES_ | xargs) > /dev/null && \
	docker run -it --network=host --rm kubetools/pgcli postgresql://$$POSTGRES_USER:$$POSTGRES_PASSWORD@localhost:$$POSTGRES_PORT/$$POSTGRES_DATABASE

.PHONY: psql
psql: # Runs psql in a container and connects to the postgres development DB
	export $$(cat .env | grep POSTGRES_ | xargs) > /dev/null && \
	docker exec -e PGHOST=localhost -e PGUSER=$$POSTGRES_USER -e PGPASSWORD=$$POSTGRES_PASSWORD -e PGDATABASE=$$POSTGRES_DATABASE -it $(PROJECT_NAME)_ctr_postgres psql ;\

.PHONY: mysql
mysql: ## Runs the MySQL CLI and connects to MySQL development DB
	export $$(cat .env | grep MYSQL_ | xargs) > /dev/null && \
	docker exec -it $(PROJECT_NAME)_ctr_mysql mysql -u $$MYSQL_USER -p$$MYSQL_PASSWORD --database $$MYSQL_DATABASE

.PHONY: mariadb
mariadb: ## Runs the MySQL CLI and connects to the MariaDB Development DB
	export $$(cat .env | grep MARIADB_ | xargs) > /dev/null && \
	docker exec -it $(PROJECT_NAME)_ctr_mariadb mysql -u $$MARIADB_USER -p$$MARIADB_PASSWORD --database $$MARIADB_DATABASE
