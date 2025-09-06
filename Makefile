.PHONY: up down restart logs app frontend postgres nginx migrate fresh seed tinker composer npm-install npm-dev npm-build

# Docker shortcuts
up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose down && docker compose up -d

logs:
	docker compose logs -f

# Containers
app:
	docker compose exec app bash

frontend:
	docker compose exec frontend sh

postgres:
	docker compose exec postgres psql -U root -d app

nginx:
	docker compose exec nginx sh

# Laravel helpers
migrate:
	docker compose exec app php artisan migrate

fresh:
	docker compose exec app php artisan migrate:fresh

seed:
	docker compose exec app php artisan db:seed

tinker:
	docker compose exec app php artisan tinker

composer:
	docker compose exec app composer install

# Frontend helpers
npm-install:
	docker compose exec frontend npm install

npm-dev:
	docker compose exec frontend npm run dev -- --host

npm-build:
	docker compose exec frontend npm run build
