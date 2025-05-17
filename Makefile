start:
	@cd docker/dev \
	&& docker compose up -d

stop:
	@cd docker/dev \
	&& docker compose stop

########################
# Init (for production)
########################
init:
	bin/console doctrine:database:create
	bin/console doctrine:migrations:migrate
	bin/console app:init-config

#############
# Containers
#############

nginx:
	@cd docker/dev \
	&& docker compose exec nginx bash

php:
	@cd docker/dev \
	&& docker compose exec php bash

mysql:
	@cd docker/dev \
	&& docker compose exec mysql bash

#############
# Tools
#############

#### External (outside the container)

phpstan:
	@cd docker/dev \
	&& docker compose exec php bash -c 'make phpstan_command'

phpunit:
	@cd docker/dev \
	&& docker compose exec php bash -c 'make create_test_db_command' \
	&& docker compose exec php bash -c 'make phpunit_command'

phpcs:
	@cd docker/dev \
	&& docker compose exec php bash -c 'make phpcs_command'

security:
	@cd docker/dev \
    && docker compose exec php bash -c 'composer audit'

schema_validate:
	@cd docker/dev \
	&& docker compose exec php bash -c 'make schema_validate_command'

schema_update:
	@cd docker/dev \
	&& docker compose exec php bash -c 'make update_db_schema_command'

db_drop:
	@cd docker/dev \
	&& docker compose exec php bash -c 'make drop_db_command'

migrate:
	@cd docker/dev \
	&& docker compose exec php bash -c 'make migrate_command'

fixtures:
	@cd docker/dev \
	&& docker compose exec php bash -c 'make load_fixtures_command'

dump_db:
	@cd docker/dev \
	&& docker compose exec mysql bash -c 'mysqldump -u root jaw -p > /var/www/html/dump.sql && `chown -R $$DOCKER_USER_UID:$$DOCKER_USER_GID /var/www/html/dump.sql`'

# To use when you want to create a new migration file. You have to run 'make migrate' just after.
make create_migration:
	@cd docker/dev \
	&& docker compose exec php bash -c 'bin/console doctrine:database:drop --force && bin/console doctrine:database:create && make migrate_command && bin/console doctrine:migration:diff'

#### Internal (inside the container)

phpstan_command:
	@APP_ENV=test bin/console cache:warmup
	vendor/bin/phpstan clear-result-cache
	php vendor/bin/phpstan analyse --memory-limit=-1

phpunit_command:
	make create_test_db_command
	APP_ENV=test bin/console cache:warmup && vendor/bin/phpunit --testdox

phpcs_command:
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src

schema_validate_command:
	APP_ENV=dev bin/console doctrine:schema:validate

update_db_schema_command:
	APP_ENV=dev bin/console doctrine:schema:update --force

drop_db_command:
	APP_ENV=dev bin/console doctrine:database:drop --force

migrate_command:
	APP_ENV=dev bin/console doctrine:migrations:migrate

create_test_db_command:
	bin/console doctrine:database:drop --force --env=test || true \
	&& bin/console doctrine:database:create --env=test \
	&& bin/console doctrine:migrations:migrate -n --env=test \
	&& bin/console doctrine:fixtures:load -n --env=test \
	&& bin/console app:add-user some_username_admin somePassword foo@bar.com "Foo BAR" --admin --env=test \
	&& bin/console app:add-user some_username_not_admin somePassword foofoo@barbar.com "Foofoo BARBAR" --env=test

load_fixtures_command:
	APP_ENV=dev bin/console doctrine:fixtures:load
