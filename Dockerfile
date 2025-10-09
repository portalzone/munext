# Stage 1: Build Vue frontend
FROM node:22-alpine AS frontend-builder

WORKDIR /frontend
COPY frontend/package*.json ./
RUN npm install
COPY frontend/ .
RUN npm run build

# Stage 2: Build PHP backend
FROM php:8.2-cli

# Install system dependencies including MySQL client
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy backend files
COPY backend/ ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy built frontend
COPY --from=frontend-builder /frontend/dist ./public

# Set proper permissions
RUN chmod -R 775 storage bootstrap/cache

# Startup script (as you have written)
RUN echo '#!/bin/bash\n\
set -e\n\
echo "==> Waiting for database connection..."\n\
sleep 10\n\
\n\
echo "==> Running database migrations..."\n\
php artisan migrate --force\n\
\n\
echo "==> Clearing caches..."\n\
php artisan config:clear\n\
php artisan cache:clear\n\
php artisan route:clear\n\
php artisan view:clear\n\
\n\
echo "==> Seeding admin user..."\n\
php artisan db:seed --class=AdminSeeder --force || echo "Admin user already exists"\n\
\n\
echo "==> Optimizing application..."\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
echo "==> Starting server on port ${PORT:-8080}..."\n\
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}\n\
' > /start.sh && chmod +x /start.sh


EXPOSE 8080

CMD ["/start.sh"]
