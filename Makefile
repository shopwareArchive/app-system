# VARIABLES
USER_ID := $(shell id -u)
GROUP_ID := $(shell id -g)
TOOLS_BIN := dev-ops/tools/vendor/bin

# TARGETS
.PHONY: help static-analysis test ecs-dry ecs-fix init install-tools administration-unit administration-lint vendor administration-e2e-open administration-e2e-cli-proxy administration-e2e-open

.DEFAULT_GOAL := help

help:
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

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
		&& ./psh.phar init-test-databases \
		&& ./psh.phar e2e:dump-db \
		&& ./psh.phar cache

administration-unit: ## run administration unit tests
	npm --prefix src/Resources/app/administration run unit

administration-lint: ## run eslint for administration
	npm --prefix src/Resources/app/administration run eslint

install-tools: | $(TOOLS_BIN) ## Installs connect dev tooling

$(TOOLS_BIN):
	composer install -d dev-ops/tools

vendor:
	composer install --no-interaction --optimize-autoloader --no-suggest --no-scripts --no-progress

e2e-cli-proxy: ## starts an express server to add additional commands for e2e tests
ifneq ($(wildcard ./src/Resources/app/e2e/cli-tools/node_modules/.),)
	npm --prefix src/Resources/app/e2e/cli-tools run start
else
	npm --prefix src/Resources/app/e2e/cli-tools install --production
	npm --prefix src/Resources/app/e2e/cli-tools run start
endif

e2e-open: ## open cypress
	- cd ../../../ && ./psh.phar cache --DB_NAME="shopware_e2e" --APP_ENV="prod"
	- npm --prefix src/Resources/app/e2e run open
	- cd ../../../ && ./psh.phar cache