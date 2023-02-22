CSFIX_PHP_BIN=PHP_CS_FIXER_IGNORE_ENV=1 php8.2
PHP_BIN=php8.2 -d zend.assertions=1 -d error_reporting=-1
COMPOSER_BIN=$(shell command -v composer)

all: csfix static-analysis test
	@echo "Done."

vendor: composer.json
	$(PHP_BIN) $(COMPOSER_BIN) update
	$(PHP_BIN) $(COMPOSER_BIN) bump
	touch vendor

.PHONY: csfix
csfix: vendor
	$(CSFIX_PHP_BIN) vendor/bin/php-cs-fixer fix -v

.PHONY: static-analysis
static-analysis: vendor
	$(PHP_BIN) vendor/bin/phpstan analyse $(PHPSTAN_ARGS)

.PHONY: test
test: vendor
	$(PHP_BIN) vendor/bin/phpunit $(PHPUNIT_ARGS)

.PHONY: mariadb-start
mariadb-start:
	docker run --publish 3306:3306 --rm --name mariadb-php-testing --env MARIADB_ROOT_PASSWORD=root_password --detach mariadb:latest

.PHONY: mariadb-stop
mariadb-stop:
	docker stop mariadb-php-testing
