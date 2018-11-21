RUN_COMMAND=docker-compose run --rm site1.local

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
	docker exec -it site1-local bash

composer-istall:
	$(RUN_COMMAND) composer install

composer-update:
	$(RUN_COMMAND) composer update

test:
	$(RUN_COMMAND) /application/bin/phpunit -c /application/phpunit.xml.dist $(PHPUNIT_FLAGS)

coveralls:
	docker-compose run --rm -e TRAVIS=$(TRAVIS) -e TRAVIS_JOB_ID=$(TRAVIS_JOB_ID) site1.local php /application/bin/php-coveralls -v


run-script:
	docker exec -it \
	site1-local \
	php /application/$(script)

site1:
	open -a "Firefox" http://localhost:8880/

site2:
	open -a "Firefox" http://localhost:8881/


