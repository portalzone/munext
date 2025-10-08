# Stage 1: Build Vue frontend
FROM node:22-alpine AS frontend-builder

WORKDIR /frontend
COPY frontend/package*.json ./
RUN npm install
COPY frontend/ .

RUN CI=false npm run build


# Stage 2: Build PHP backend
FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY backend/ ./

RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY --from=frontend-builder /frontend/dist ./public

RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

EXPOSE 8080

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
