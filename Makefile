# VARIABLES
USER_ID := $(shell id -u)
GROUP_ID := $(shell id -g)
TOOLS_BIN := dev-ops/tools/vendor/bin

# TARGETS
.PHONY: help static-analysis test ecs-dry ecs-fix init install-tools administration-unit administration-lint vendor

.DEFAULT_GOAL := help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

static-analysis: | install-tools vendor ## runs psalm and phpstan
	$(TOOLS_BIN)/psalm --output-format=compact
	$(TOOLS_BIN)/phpstan analyze --configuration phpstan.neon src
	$(TOOLS_BIN)/phpinsights --no-interaction

test: ## runs phpunit
	composer dump-autoload
	php -d pcov.enabled=1 -d pcov.directory=./src ../../../vendor/bin/phpunit \
       --configuration phpunit.xml.dist \
       --coverage-clover build/artifacts/phpunit.clover.xml \
       --coverage-html build/artifacts/phpunit-coverage-html

ecs-dry: | install-tools vendor  ## runs easy coding standard in dry mode
	$(TOOLS_BIN)/ecs check .

ecs-fix: | install-tools vendor  ## runs easy coding standard and fixes issues
	$(TOOLS_BIN)/ecs check . --fix

init: ## activates the plugin and dumps test-db
	- cd ../../../ \
		&& ./psh.phar init \
		&& php bin/console plugin:install --activate SaasConnect \
		&& ./psh.phar init-test-databases

administration-unit:
	npm --prefix src/Resources/app/administration run unit

administration-lint:
	npm --prefix src/Resources/app/administration run eslint

install-tools: | $(TOOLS_BIN) ## Installs connect dev tooling

$(TOOLS_BIN):
	composer install -d dev-ops/tools

vendor:
	composer install --no-interaction --optimize-autoloader --no-suggest --no-scripts --no-progress
