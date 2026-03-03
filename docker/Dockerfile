FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libsqlite3-dev \
    procps \
    sysstat \
    net-tools \
    iproute2 \
    && docker-php-ext-install zip pdo pdo_sqlite pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Create a minimal Laravel app
RUN composer create-project laravel/laravel . --prefer-dist --no-interaction

# Copy local packages
COPY laravel-health /packages/laravel-health
COPY system-metrics /packages/system-metrics

# Add path repositories with version overrides and require the package
RUN composer config repositories.system-metrics '{"type": "path", "url": "/packages/system-metrics", "options": {"versions": {"cboxdk/system-metrics": "2.1.99"}}}' --json \
    && composer config repositories.laravel-health '{"type": "path", "url": "/packages/laravel-health", "options": {"versions": {"cboxdk/laravel-health": "1.0.99"}}}' --json \
    && composer require cboxdk/laravel-health:@dev --no-interaction

# Create SQLite database
RUN touch database/database.sqlite

# Configure .env
RUN sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env \
    && sed -i '/^DB_HOST/d; /^DB_PORT/d; /^DB_DATABASE=(?!database)/d; /^DB_USERNAME/d; /^DB_PASSWORD/d' .env \
    && echo "DB_DATABASE=/app/database/database.sqlite" >> .env \
    && echo "HEALTH_ENABLED=true" >> .env \
    && echo "HEALTH_TOKEN=test-token" >> .env \
    && echo "CACHE_STORE=array" >> .env \
    && echo "QUEUE_CONNECTION=sync" >> .env

# Publish health config and enable UI
RUN php artisan vendor:publish --tag=health-config --force \
    && sed -i "s/'ui' => \['path' => '\/ui', 'enabled' => false\]/'ui' => ['path' => '\/ui', 'enabled' => true]/" config/health.php

# Run migrations
RUN php artisan migrate --force

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
