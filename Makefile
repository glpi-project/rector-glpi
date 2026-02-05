PHP = docker compose run --rm app

# Helper variables
_TITLE := "\033[32m[%s]\033[0m %s\n" # Green text
_ERROR := "\033[31m[%s]\033[0m %s\n" # Red text

##
## This Makefile is used for *local development* only.
##

## —— General ——————————————————————————————————————————————————————————————————————————————————————
.DEFAULT_GOAL := help
help: ## Show this help message
	@grep -hE '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-25s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help

## —— Dependencies —————————————————————————————————————————————————————————————————————————————————
composer: ## Run a composer command, e.g.: make composer c='require org/package'
	@$(eval c ?=)
	docker run --rm --interactive --tty --volume $PWD:/app --user $(id -u):$(id -g) --workdir /app composer $(c)
.PHONY: composer

## —— Code analysis ————————————————————————————————————————————————————————————————————————————————
phpstan: ## Run phpstan
	@$(PHP) php vendor/bin/phpstan
.PHONY: phpstan

parallel-lint: ## Run PHP lint
	@$(PHP) php vendor/bin/parallel-lint \
		--show-deprecated \
		--colors \
		--exclude ./vendor/ \
		.
.PHONY: parallel-lint

phpcsfixer-check: ## Check for php coding standards issues
	@$(PHP) vendor/bin/php-cs-fixer check --diff -vvv
.PHONY: phpcsfixer-check

phpcsfixer-fix: ## Fix php coding standards issues
	@$(PHP) vendor/bin/php-cs-fixer fix
.PHONY: phpcsfixer-fix

lint-check: parallel-lint phpcsfixer-check phpstan ## Run all linters checks
.PHONY: lint-check

lint-fix: parallel-lint phpcsfixer-fix phpstan ## Run all linters checks and apply fixes
.PHONY: lint-fix

## —— Testing ——————————————————————————————————————————————————————————————————————————————————————
phpunit: ## Run phpunits tests, e.g.: make phpunit c='tests/Rector/Glpi120x'
	@$(eval c ?=)
	@$(PHP) php vendor/bin/phpunit $(c)
.PHONY: phpunit
