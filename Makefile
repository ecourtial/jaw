start:
	@cd docker/dev \
	&& docker-compose up -d \
	&& docker-compose exec php bash -c 'chown -R 1000:1000 /var/www'

stop:
	@cd docker/dev \
	&& docker-compose stop

#############
# Containers
#############

nginx:
	@cd docker/dev \
	&& docker-compose exec nginx bash

php:
	@cd docker/dev \
	&& docker-compose exec php bash

mysql:
	@cd docker/dev \
	&& docker-compose exec mysql bash

#############
# Tools
#############

#### External (outside the container)

phpstan:
	@cd docker/dev \
	&& docker-compose exec php bash -c 'make phpstan-command'

phpunit:
	@cd docker/dev \
	&& docker-compose exec php bash -c 'make phpunit-command'

phpcs:
	@cd docker/dev \
	&& docker-compose exec php bash -c 'make phpcs-command'


#### Internal (inside the container)

phpstan-command:
	@APP_ENV=test bin/console cache:warmup
	vendor/bin/phpstan clear-result-cache
	php vendor/bin/phpstan analyse --memory-limit=-1

phpunit-command:
	APP_ENV=test bin/console cache:warmup && vendor/bin/phpunit --testdox

phpcs-command:
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src
