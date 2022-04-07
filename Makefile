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
	&& docker-compose exec php bash -c 'make create_test_db_command' \
	&& docker-compose exec php bash -c 'make phpunit_command'

phpcs:
	@cd docker/dev \
	&& docker-compose exec php bash -c 'make phpcs_command'

security:
	@cd docker/dev \
    && docker-compose exec php bash -c '/usr/local/bin/local-php-security-checker'

schema_validate:
	@cd docker/dev \
	&& docker-compose exec php bash -c 'make schema_validate_command'

migrate:
	@cd docker/dev \
	&& docker-compose exec php bash -c 'make migrate_command'

fixtures:
	@cd docker/dev \
	&& docker-compose exec php bash -c 'make load_fixtures_command'

#### Internal (inside the container)

phpstan_command:
	@APP_ENV=test bin/console cache:warmup
	vendor/bin/phpstan clear-result-cache
	php vendor/bin/phpstan analyse --memory-limit=-1

phpunit_command:
	APP_ENV=test bin/console cache:warmup && vendor/bin/phpunit --testdox

phpcs_command:
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src

schema_validate_command:
	APP_ENV=test bin/console doctrine:schema:validate
	APP_ENV=test bin/console doctrine:ensure-production-settings

migrate_command:
	APP_ENV=prod bin/console doctrine:migrations:migrate

create_test_db_command:
	bin/console doctrine:database:drop --force --env=test || true \
	&& bin/console doctrine:database:create --env=test \
	&& bin/console doctrine:migrations:migrate -n --env=test \
	&& bin/console app:add-user some_username_admin somePassword foo@bar.com "Foo BAR" --admin --env=test \
	&& bin/console app:add-user some_username_not_admin somePassword foofoo@barbar.com "Foofoo BARBAR" --env=test

load_fixtures_command:
	bin/console doctrine:fixtures:load
