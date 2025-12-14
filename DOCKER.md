# Docker Setup for Laravel Application

This Laravel application is configured to run with Docker Compose, including Nginx with SSL, PostgreSQL, Redis, and Ollama.

## Services

- **nginx**: Web server running on ports 80 (redirects to HTTPS) and 443 (HTTPS)
- **app**: Laravel PHP-FPM application
- **pgsql**: PostgreSQL 16 database
- **redis**: Redis 7 for caching and sessions
- **ollama**: Ollama AI service

All services have `restart: "no"` policy, meaning they won't auto-restart unless explicitly started.

## Prerequisites

- Docker
- Docker Compose

## Setup Instructions

### 1. Build and Start Services

```bash
# Build the Docker images
docker-compose build

# Start all services
docker-compose up -d

# Or build and start in one command
docker-compose up -d --build
```

### 2. Install Dependencies (First Time Setup)

```bash
# Install Composer dependencies
docker-compose exec app composer install

# Generate application key (if not already set)
docker-compose exec app php artisan key:generate

# Run database migrations
docker-compose exec app php artisan migrate

# Seed the database (optional)
docker-compose exec app php artisan db:seed
```

### 3. Set Permissions

```bash
# Ensure storage and cache directories are writable
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

## Accessing the Application

- **HTTPS**: https://localhost
- **HTTP**: http://localhost (redirects to HTTPS)

**Note**: Since we're using self-signed SSL certificates, your browser will show a security warning. You can safely proceed by accepting the certificate.

## Service Management

### Start Services
```bash
docker-compose up -d
```

### Stop Services
```bash
docker-compose down
```

### Stop and Remove Volumes (Clean Slate)
```bash
docker-compose down -v
```

### View Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f pgsql
```

### Restart a Service
```bash
docker-compose restart app
docker-compose restart nginx
```

## Database Access

### Connect to PostgreSQL
```bash
# Using Docker
docker-compose exec pgsql psql -U laravel -d laravel

# From host machine
psql -h localhost -U laravel -d laravel
# Password: secret
```

### Database Credentials
- Host: `localhost` (from host) or `pgsql` (from containers)
- Port: `5432`
- Database: `laravel`
- Username: `laravel`
- Password: `secret`

## Redis Access

```bash
# Connect to Redis CLI
docker-compose exec redis redis-cli

# Test connection
docker-compose exec redis redis-cli ping
```

## Ollama Usage

Ollama is available at `http://localhost:11434` or from containers at `http://ollama:11434`.

```bash
# Pull a model
docker-compose exec ollama ollama pull llama2

# Run a model
docker-compose exec ollama ollama run llama2

# List models
docker-compose exec ollama ollama list
```

## Running Artisan Commands

```bash
# General format
docker-compose exec app php artisan <command>

# Examples
docker-compose exec app php artisan migrate
docker-compose exec app php artisan tinker
docker-compose exec app php artisan queue:work
docker-compose exec app php artisan cache:clear
```

## Running Composer Commands

```bash
docker-compose exec app composer install
docker-compose exec app composer update
docker-compose exec app composer require package/name
```

## SSL Certificates

Self-signed SSL certificates are generated in the `docker/ssl/` directory. These are for development purposes only.

To regenerate certificates:
```bash
cd docker/ssl
./generate-ssl.sh
```

## Troubleshooting

### Permission Issues
```bash
docker-compose exec app chown -R www-data:www-data /var/www/html
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Clear Application Cache
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Rebuild Containers
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Database Connection Issues
Make sure the database service is running and healthy:
```bash
docker-compose ps
docker-compose logs pgsql
```

## Environment Variables

Key environment variables in `.env`:

```
APP_URL=https://localhost

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## Production Deployment

For production:
1. Replace self-signed certificates with valid SSL certificates from a CA
2. Change `restart: "no"` to `restart: unless-stopped` in docker-compose.yml
3. Set `APP_DEBUG=false` in .env
4. Set `APP_ENV=production` in .env
5. Use strong database passwords
6. Configure proper backup strategies for volumes
