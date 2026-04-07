FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    libsqlite3-dev libzip-dev zip unzip git curl \
    && docker-php-ext-install pdo pdo_sqlite zip pcntl bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Node.js for Vite build
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm ci && npm run build

RUN mkdir -p /data storage/logs storage/framework/cache \
             storage/framework/sessions storage/framework/views \
             bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

EXPOSE ${PORT:-8080}

CMD ["sh", "-c", "php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
