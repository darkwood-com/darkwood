ENV_DIR := tools/env
NIX_FLAGS := --extra-experimental-features nix-command --extra-experimental-features flakes
NIX := nix develop $(NIX_FLAGS) ./$(ENV_DIR)
DOCKER_COMPOSE ?= docker compose

EXEC_PHP        = php -d memory_limit=-1
CONSOLE         = $(EXEC_PHP) bin/console
COMPOSER        = composer
SYMFONY         = symfony

##
##Dev
##-------------

nix: ## Enter Nix dev shell (tools/env flake)
	$(NIX)

docker-up: ## Start Docker services from tools/env (Postgres)
	cd $(ENV_DIR) && $(DOCKER_COMPOSE) up -d

docker-down: ## Stop Docker services from tools/env
	cd $(ENV_DIR) && $(DOCKER_COMPOSE) down

docker: docker-up ## Alias for docker-up

env: docker-up ## Start Docker services then enter Nix dev shell
	$(NIX)

console: ## Run Symfony console (ARGS="cache:clear")
	$(NIX) --command php bin/console $(ARGS)

install: ## Install dependencies
	$(NIX) --command composer install

serve: ## Run Symfony development server
	$(NIX) --command ./bin/console server:start --port=8000

stop: ## Stop Symfony development server
	$(NIX) --command ./bin/console server:stop

##
##DevOps
##-------------

php-cs-fixer: ## Check and fix coding styles using PHP CS Fixer
	composer php-cs-fixer

phpstan: ## Execute PHPStan analysis
	composer phpstan

phpunit: ## Launch PHPUnit test suite
	composer phpunit

# DEFAULT
.DEFAULT_GOAL := help
.PHONY: help nix docker docker-up docker-down env console install serve stop php-cs-fixer phpstan phpunit
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

##
