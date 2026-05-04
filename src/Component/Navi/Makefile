NIX=nix develop --extra-experimental-features nix-command --extra-experimental-features flakes
EXEC_PHP        = php -d memory_limit=-1
CONSOLE         = $(EXEC_PHP) bin/console
COMPOSER        = composer
SYMFONY         = symfony

##
##Dev
##-------------

nix: ## Start nix development
	$(NIX)

console: ## Start PHP console
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
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help

##
