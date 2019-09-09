.PHONY: down build up ssh composer-istall composer-update test-php7.3 test-php7.2 test-php7.1 test coveralls run-script site1 site2

RUN_COMMAND=docker-compose run --rm site1.local

RUN_TESTS=docker run --rm --interactive --tty --network crawlzone_default -v `pwd`:/application

PHPUNIT_COMMAND=/application/bin/phpunit -c /application/phpunit.xml.dist --no-coverage $(PHPUNIT_FLAGS)

PHPUNIT_FLAGS=

ifdef filter
	PHPUNIT_FLAGS+=--filter=$(filter)
endif

default: init

init: down build up

down:
	docker-compose down

build:
	docker-compose build

up:
	docker-compose up -d

ssh:
	docker-compose exec site1.local bash

yarn-install:
	$(RUN_COMMAND) yarn install

composer-istall:
	$(RUN_COMMAND) composer install

composer-update:
	$(RUN_COMMAND) composer update

test-php7.1:
	$(RUN_TESTS) php:7.1-cli $(PHPUNIT_COMMAND) --no-coverage --exclude-group javascript

test-php7.2:
	$(RUN_TESTS) php:7.2-cli $(PHPUNIT_COMMAND) --no-coverage --exclude-group javascript

test-php7.3-javascript:
	$(RUN_COMMAND) $(PHPUNIT_COMMAND)

test: test-php7.3-javascript test-php7.2 test-php7.1 run-javascript-test

coveralls:
	docker-compose run --rm -e TRAVIS=$(TRAVIS) -e TRAVIS_JOB_ID=$(TRAVIS_JOB_ID) site1.local php /application/bin/php-coveralls -v


run-script:
	docker exec -it \
	site1-local \
	php /application/$(script)

run-javascript-test:
	$(RUN_COMMAND) node src/Tests/puppeteer/puppeter-test.js

site1:
	open -a "Firefox" http://localhost:8880/

site2:
	open -a "Firefox" http://localhost:8881/


