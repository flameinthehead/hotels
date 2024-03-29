up:
	docker-compose up -d --build && docker-compose ps

down:
	docker-compose down -v --remove-orphans

exec:
	docker exec -it hotels_php_cli bash

tinker:
	docker-compose exec php-cli php artisan tinker

migrate:
	docker-compose exec php-cli php artisan migrate

rollback-last:
	docker-compose exec php-cli php artisan migrate:rollback --step=1

parse-way:
	docker-compose exec php-cli php artisan flights:cheapest --origin=MOW --destination=IST

parse-popular-cities:
	docker-compose exec php-cli php artisan flights:cheapest

clear-expired:
	docker-compose exec php-cli php artisan flights:expire

clear-cache:
	docker-compose exec php-cli php artisan cache:clear
