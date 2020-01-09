# VARIABLES
USER_ID := $(shell id -u)
GROUP_ID := $(shell id -g)

# TARGETS
.PHONY: help static-analysis test cs-fixer init

.DEFAULT_GOAL := help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'


static-analysis: ## runs psalm and phpstan
	docker-compose run -w '/app' -u "$(USER_ID):$(GROUP_ID)" static-analysis composer install --ignore-platform-reqs --no-interaction --optimize-autoloader --no-suggest --no-scripts --no-progress
	docker-compose run -w '/app' -u "$(USER_ID):$(GROUP_ID)" static-analysis psalm --output-format=compact
	docker-compose run -w '/app' -u "$(USER_ID):$(GROUP_ID)" static-analysis phpstan analyze --configuration phpstan.neon src

test: ## runs phpunit
	composer dump-autoload
	php -d pcov.enabled=1 ../../../vendor/bin/phpunit \
       --configuration phpunit.xml.dist \
       --coverage-clover build/artifacts/phpunit.clover.xml

cs-fixer: ## fixes all php files currently marked edited by git
	docker-compose run -w '/app' -u "$(USER_ID):$(GROUP_ID)" static-analysis composer install --ignore-platform-reqs --no-interaction --optimize-autoloader --no-suggest --no-scripts --no-progress
	docker-compose run -w '/app' -u "$(USER_ID):$(GROUP_ID)" static-analysis php dev-ops/scripts/fixCodeStyle.php

cs-fixer-all: ## fixes all php
	docker-compose run -w '/app' -u "$(USER_ID):$(GROUP_ID)" static-analysis composer install --ignore-platform-reqs --no-interaction --optimize-autoloader --no-suggest --no-scripts --no-progress
	docker-compose run -w '/app' -u "$(USER_ID):$(GROUP_ID)" static-analysis php-cs-fixer fix  -v --allow-risky=yes .

init: ## activates the plugin and dumps test-db
	- cd ../../../ \
		&& ./psh.phar init \
		&& php bin/console plugin:install --activate SaasConnect \
		&& ./psh.phar init-test-databases
