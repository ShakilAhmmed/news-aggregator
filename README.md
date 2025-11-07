# News Aggregator

Platform for Aggregate news daily from several sources.

---

## Installation

```bash
# Clone the repository
git clone https://github.com/ShakilAhmmed/news-aggregator.git
cd news-aggregator
```

## Copy environment file

```bash
cp .env.example .env
```

#### Write your NGINX and PHPMYADMIN PORT in .env and Set username and password for database

##### Do not use @ in the DB_PASSWORD [ex: "!!2083!!"]

## Start containers

```bash
sudo docker-compose up -d
```

## Access application container

```bash
sudo docker exec -it news-aggregator-backend bash
```

## Install dependencies

```bash
composer install
```

## Generate application key

```bash
php artisan key:generate
php artisan storage:link
```

## Run migrations and seeders

```bash
php artisan migrate --seed
```

## API Documentation in

```bash
http://.........../docs/api
```

## Run Tests (Pest)

```bash
sudo docker exec -it news-aggregator-backend bash
php artisan test
```

## Static Analysis & Linting

```bash
sudo docker exec -it news-aggregator-backend bash
vendor/bin/phpstan analyse --memory-limit=1G
```

## Code style check

```bash
sudo docker exec -it news-aggregator-backend bash
vendor/bin/pint --test
```

## Code quality report

```bash
sudo docker exec -it news-aggregator-backend bash
php artisan insights
```

### Application Structure

```bash
This application follows a modular and action-driven architecture designed for clarity, scalability, and separation of concerns.
Below is an overview of the core structure inside the app/ directory:
app/Actions/V1:
    - All the Actions which are related to Api version 1
Enums:
    - All Application Related Constants
Http/Controllers/Api/V1:
    - All the Controller Related to Api version 1.
Http/Requests/Api/V1:
    - All the Form Requests Related to Api version 1.
Http/Resources/Api/V1:
    - All the Api Resources Related to Api version 1.
app/Contracts:
  - An Interface which should be followed by concrete Implementation (New Source)
```

## Developer Tools

```
    - Laravel Pint
    - Larastan
    - PestPHP
    - Symfony Dump Server
    - PHP Insights
```
```bash
Github Action will trigger .github/workflows/workflow.yml (Larastan,Pest,Laravel Pint)
```

## TODO

```bash
    - Observability & Monitoring - Integrate OpenTelemetry
    - Rate Limiting Strategy
    - Laravel Pint Set As PreCommit Hook
```
