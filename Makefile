# VARIABLES
USER_ID := $(shell id -u)
GROUP_ID := $(shell id -g)

# TARGETS
.PHONY: help static-analysis

.DEFAULT_GOAL := help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'


static-analysis: ## runs psalm and phpstan
	docker-compose run -w '/app' -u "$(USER_ID):$(GROUP_ID)" static-analysis composer install --ignore-platform-reqs --no-interaction --optimize-autoloader --no-suggest --no-scripts --no-progress
	docker-compose run -w '/app' -u "$(USER_ID):$(GROUP_ID)" static-analysis psalm --output-format=compact
	docker-compose run -w '/app' -u "$(USER_ID):$(GROUP_ID)" static-analysis phpstan analyze --configuration phpstan.neon src
