all: csfix static-analysis test
	@echo "Done."

vendor: composer.json
	composer update
	touch vendor

.PHONY: csfix
csfix: vendor
	vendor/bin/php-cs-fixer fix --verbose

.PHONY: static-analysis
static-analysis: vendor
	php -d zend.assertions=1 vendor/bin/phpstan analyse

.PHONY: test
test: vendor
	php -d zend.assertions=1 vendor/bin/phpunit

.PHONY: mariadb-start
mariadb-start:
	docker run --publish 3306:3306 --rm --name mariadb-php-testing --env MARIADB_ROOT_PASSWORD=root_password --detach mariadb:latest

.PHONY: mariadb-stop
mariadb-stop:
	docker stop mariadb-php-testing
