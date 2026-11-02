.PHONY: up down logs shell test

up:
	docker compose up -d --build

down:
	docker compose down

logs:
	docker compose logs -f

shell:
	docker compose exec app sh

test:
	docker compose exec app sh docker/reset-test-db.sh
	docker compose exec app php bin/console cache:clear --env=test
	docker compose exec app php bin/phpunit
