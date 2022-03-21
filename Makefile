start:
	@cd docker \
	&& docker-compose up -d \
	&& symfony server:start

mysql:
	@cd docker \
	&& docker-compose exec mysql bash
