.PHONY: all help test %

all: help

%: ; @:

help: ## Autogenerated list of commands
	@echo "Usage: make [command]"
	@echo
	@echo "Commands:"
	@echo
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-25s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

DOCKER_IMAGE := dev-telephonic:dev
COMPOSER_DEPENDENCY_PATH := $(HOME)/dependency

build: ## Build the project
	@docker build -t $(DOCKER_IMAGE) --target base .
	@$(MAKE) composer-install

composer-install: ## Install composer dependencies
	@docker run --rm --user "${UID}":"${GID}" -v "${PWD}":/app -w /app $(DOCKER_IMAGE) composer install

composer-update: ## Update composer dependencies
	@docker run --rm --user "${UID}":"${GID}" -v "${PWD}":/app -v "$(COMPOSER_DEPENDENCY_PATH)":/dependency -w /app $(DOCKER_IMAGE) composer update $(filter-out $@,$(MAKECMDGOALS))

composer-require: ## Require composer dependencies
	@docker run --rm --tty --user "${UID}":"${GID}" -v "${PWD}":/app -v "$(COMPOSER_DEPENDENCY_PATH)":/dependency -w /app $(DOCKER_IMAGE) composer require $(filter-out $@,$(MAKECMDGOALS))

composer-require-dev: ## Require composer dev dependencies
	@docker run --rm --tty --user "${UID}":"${GID}" -v "${PWD}":/app -v "$(COMPOSER_DEPENDENCY_PATH)":/dependency -w /app $(DOCKER_IMAGE) composer require --dev $(filter-out $@,$(MAKECMDGOALS))

composer-why: ## Show why a package is installed
	@docker run --rm --user "${UID}":"${GID}" -v "${PWD}":/app -v "$(COMPOSER_DEPENDENCY_PATH)":/dependency -w /app $(DOCKER_IMAGE) composer why $(filter-out $@,$(MAKECMDGOALS))

test: ## Run the tests
	@docker run --rm --tty --user "${UID}":"${GID}" -v "${PWD}":/app -w /app $(DOCKER_IMAGE) php vendor/bin/phpunit --colors=always --no-coverage --stop-on-failure

test-coverage: ## Run the tests with coverage
	@docker run --rm --tty --user "${UID}":"${GID}" -e "XDEBUG_MODE=coverage" -v "${PWD}":/app -w /app $(DOCKER_IMAGE) php vendor/bin/phpunit --colors=always --stop-on-failure

run-example: up ## Run the project
	@docker compose exec app php $(filter-out $@,$(MAKECMDGOALS))

up: ## Start the project
	@docker compose up -d

down: ## Stop the project
	@docker compose down

# RELEASES -------------------------------------------------------------------------------------------------------------
.ONESHELL:
release: release-start release-finish ## Generate Changelog file and new version tag and upload it to the server

release-start:
	@cz ch --dry-run
	@cz bump --dry-run
	@echo "Confirmation required:"
	@echo "If above info is correct press any key to continue. If not, press CTRL+C to stop publishing a release"
	@read none
	@cz bump --changelog -at

release-finish:
	@echo "Uploading release to git origin"
	@git push --no-verify && git push --tags --no-verify
