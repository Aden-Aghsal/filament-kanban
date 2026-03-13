# Filament Project Kanban

This is a simple Project Management app with a Kanban board, built using Laravel and FilamentPHP.

## Installation and Usage

* Clone or download the repository
* `composer install`
* `cp .env.example .env`
* `php artisan key:generate`
* `php artisan migrate`
* `npm install && npm run build`
* `php artisan serve`

## Queue Worker

The app uses queued notifications. Run a worker in the background:

* Windows: `scripts/queue-worker.bat` or `scripts/queue-worker.ps1`
* Windows Task Scheduler: `scripts/register-queue-task.ps1` (auto-fills paths from project root)
  Examples:
  `powershell -ExecutionPolicy Bypass -File scripts/register-queue-task.ps1 -TaskName "LaravelQueueWorker" -Queue "database" -Sleep 3 -Tries 3 -Timeout 120`
  `powershell -ExecutionPolicy Bypass -File scripts/register-queue-task.ps1 -Trigger Startup -RunAsSystem`
* Linux (Supervisor): see `deploy/supervisor/laravel-queue.conf`
* Linux (systemd): see `deploy/systemd/laravel-queue.service`
