start:
	@cd docker/dev \
	&& docker-compose up -d

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
	&& docker-compose exec php bash -c 'make phpstan_command'

phpunit:
	@cd docker/dev \
	&& docker-compose exec php bash -c 'make phpunit_command'

phpcs:
	@cd docker/dev \
	&& docker-compose exec php bash -c 'make phpcs_command'

schema_validate:
	@cd docker/dev \
	&& docker-compose exec php bash -c 'make schema_validate_command'

migrate:
	@cd docker/dev \
	&& docker-compose exec php bash -c 'make migrate_command'

#### Internal (inside the container)

phpstan_command:
	@APP_ENV=test bin/console cache:warmup
	vendor/bin/phpstan clear-result-cache
	php vendor/bin/phpstan analyse --memory-limit=-1

phpunit_command:
	APP_ENV=test bin/console cache:warmup && vendor/bin/phpunit --testdox

phpcs-command:
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src

schema_validate_command:
	bin/console doctrine:schema:validate
	APP_ENV=prod bin/console doctrine:ensure-production-settings

migrate_command:
	APP_ENV=prod bin/console doctrine:migrations:migrate
