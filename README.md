# Filament Project Kanban

This is a simple Project Management app with a Kanban board, built using Laravel, FilamentPHP, and Docker.

## Installation and Usage

* Clone the repository
* `cp .env.example .env` (Configure `DB_CONNECTION=pgsql` and Google Auth)
* `docker-compose up -d --build`
* `docker-compose exec app composer install`
* `docker-compose exec app php artisan key:generate`
* `docker-compose exec app php artisan storage:link`
* `docker-compose exec app php artisan migrate:fresh --seed`
* `docker-compose exec app npm install && docker-compose exec app npm run build`
* Access at **http://127.0.0.1**

## Credentials

* **Admin:** `admin@admin.com` / `password`
* **Member:** `member@member.com` / `password`
