start:
	@cd docker \
	&& docker-compose up -d \
	&& cd .. \
	&& symfony server:start

mysql:
	@cd docker \
	&& docker-compose exec mysql bash

phpunit:
	vendor/bin/phpunit --testdox

phpcs:
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src

phpstan:
	@APP_ENV=test bin/console cache:warmup \
	&& vendor/bin/phpstan clear-result-cache \
	&& vendor/bin/phpstan analyse --memory-limit=-1
