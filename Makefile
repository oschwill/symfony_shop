# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php
ELASTICSEARCH_CONT = $(DOCKER_COMP) exec elasticsearch

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP) bin/console
ELASTICSEARCH = $(ELASTICSEARCH_CONT) curl -X GET "localhost:9200/"

# Misc
.DEFAULT_GOAL = help
.PHONY        : help build up start down logs sh composer vendor sf cc test setup migrate fixtures npm-install elasticsearch

## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMP) up --detach

start: build up ## Build and start the containers

down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the FrankenPHP container
	@$(PHP_CONT) sh

test: ## Start tests with phpunit, pass the parameter "c=" to add options to phpunit, example: make test c="--group e2e --stop-on-failure"
	@$(eval c ?=)
	@$(DOCKER_COMP) exec php sh -c 'export DATABASE_URL=sqlite:///:memory: && export APP_ENV=test && ./vendor/bin/phpunit $(c)'

test-verbose:
	@$(eval c ?=)
	@$(DOCKER_COMP) exec php sh -c 'export DATABASE_URL=sqlite:///:memory: && export APP_ENV=test && ./vendor/bin/phpunit --debug'


## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

## Composer Packages installieren
vendor:
	composer install --prefer-dist --no-progress --no-scripts --no-interaction

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf

## —— Projekt-Setup 🚀 ——————————————————————————————————————————————————————————————
setup: build vendor npm-install up migrate fixtures elasticsearch-create elasticsearch-populate  ## Komplettes Projekt Setup

npm-install: ## Installiert npm-Pakete
	@npm install

migrate: ## Führt die Datenbankmigrationen aus
	@$(SYMFONY) doctrine:migrations:migrate --no-interaction

fixtures: ## Lädt die Data Fixtures
	@$(SYMFONY) doctrine:fixtures:load --no-interaction

elasticsearch-create: ## Erstellt den Elasticsearch-Index
	@$(SYMFONY) fos:elastica:create

elasticsearch-populate: ## populated den Shiaat
	@$(SYMFONY) fos:elastica:populate

## —— Elasticsearch 🕵️‍♂️ ———————————————————————————————————————————————
es-logs: ## Show logs from the Elasticsearch container
	@$(DOCKER_COMP) logs elasticsearch

es-status: ## Check the status of Elasticsearch
	@$(ELASTICSEARCH_CONT) curl -H "Content-Type: application/json" -X GET "http://localhost:9200/"

es-status-curl: ## Check the status of Elasticsearch using a curl command
	@docker-compose exec -T elasticsearch curl -X GET "localhost:9200/"

es-get-index: ## holt unseren index
	@$(ELASTICSEARCH_CONT) curl -X GET "http://localhost:9200/products/_search?pretty"

es-get-example:
	@$(ELASTICSEARCH_CONT) curl -X GET "http://localhost:9200/products/_search" -H 'Content-Type: application/json' -d '{"query": {"match": {"title": "asperiores"}}}'
