DOCKER_COMPOSE ?= docker compose
USER_ID ?= $(shell id -u)
GROUP_ID ?= $(shell id -g)
DOCKER_USER ?= "$(USER_ID):$(GROUP_ID)"
ENV ?= "dev"
PREFIX ?= "fakeddit"
DB_NAME="fakeddit"

init:
	@cp .env .env.local
	@$(MAKE) up-build
	@echo "Waiting for the database to be ready..."
	@sleep 5
	@echo "Installing PHP dependencies..."
	@docker compose exec -T php composer install --no-scripts
	@$(MAKE) db
	@docker compose exec -T php rm -rf var/cache

db-reset:
	@echo "DELETE DB..."
	@docker compose exec -T mariadb mysql -uroot -proot -e "DROP database IF EXISTS fakeddit;"

	@echo "CREATE DB..."
	@docker compose exec -T php php bin/console doctrine:database:create
	@docker compose exec -T php php bin/console d:m:m -n

	@echo "Importing initial database structure and data..."
	@docker compose exec -T mariadb mysql -uroot -proot $(DB_NAME) < ./docker/fakeddit.sql
	@echo "Database import completed."

db:
	@echo "DELETE DB..."
	@docker compose exec -T mariadb mysql -uroot -proot -e "DROP database IF EXISTS fakeddit;"

	@echo "CREATE DB..."
	@docker compose exec -T php php bin/console doctrine:database:create
	@docker compose exec -T php php bin/console d:m:m -n

	@echo "Importing initial database structure and data..."
	@docker compose exec -T mariadb mysql -uroot -proot $(DB_NAME) < ./docker/fakeddit.sql
	@echo "Database import completed."

up:
	@USER_ID=$(USER_ID) GROUP_ID=$(GROUP_ID) docker compose up -d

up-build:
	@USER_ID=$(USER_ID) GROUP_ID=$(GROUP_ID) docker compose up -d --build

up-build-linux:
	@docker compose down
	@USER_ID=$(USER_ID) GROUP_ID=$(GROUP_ID) docker compose build --no-cache && USER_ID=$(USER_ID) GROUP_ID=$(GROUP_ID) docker compose up -d

down:
	@docker compose down

php:
	@docker compose exec php bash

node:
	@docker compose exec node bash

l-node:
	@docker compose logs node -f
