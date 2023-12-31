.PHONY: all help test %

all: help

%: ; @:

help: ## Autogenerated list of commands
	@echo "Usage: make [command]"
	@echo
	@echo "Commands:"
	@echo
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-25s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build the project
	@docker build -t dev-telephonic:dev --target dev .
	@$(MAKE) composer-install

composer-install: ## Install composer dependencies
	@docker run --rm --user "${UID}":"${GID}" -v "${PWD}":/app -w /app dev-telephonic:dev composer install

composer-update: ## Update composer dependencies
	@docker run --rm --user "${UID}":"${GID}" -v "${PWD}":/app -v /Users/jamuriano/personal-workspace/opentelemetry-php-cloud-trace-exporter:/dependency -w /app dev-telephonic:dev composer update $(filter-out $@,$(MAKECMDGOALS))

composer-require: ## Require composer dependencies
	@docker run --rm --tty --user "${UID}":"${GID}" -v "${PWD}":/app -v /Users/jamuriano/personal-workspace/opentelemetry-php-cloud-trace-exporter:/dependency -w /app dev-telephonic:dev composer require $(filter-out $@,$(MAKECMDGOALS))

composer-require-dev: ## Require composer dev dependencies
	@docker run --rm --tty --user "${UID}":"${GID}" -v "${PWD}":/app -v /Users/jamuriano/personal-workspace/opentelemetry-php-cloud-trace-exporter:/dependency -w /app dev-telephonic:dev composer require --dev $(filter-out $@,$(MAKECMDGOALS))

composer-why: ## Show why a package is installed
	@docker run --rm --user "${UID}":"${GID}" -v "${PWD}":/app -v /Users/jamuriano/personal-workspace/opentelemetry-php-cloud-trace-exporter:/dependency -w /app dev-telephonic:dev composer why $(filter-out $@,$(MAKECMDGOALS))


test: ## Run the tests
	@docker run --rm --tty --user "${UID}":"${GID}" -v "${PWD}":/app -w /app dev-telephonic:dev php vendor/bin/phpunit --colors=always --no-coverage --stop-on-failure

test-coverage: ## Run the tests with coverage
	@docker run --rm --tty --user "${UID}":"${GID}" -e "XDEBUG_MODE=coverage" -v "${PWD}":/app -w /app dev-telephonic:dev php vendor/bin/phpunit --colors=always --stop-on-failure

run-example: up ## Run the project
	@docker compose exec app php $(filter-out $@,$(MAKECMDGOALS));

up: ## Start the project
	@docker compose up -d

down: ## Stop the project
	@docker compose down
