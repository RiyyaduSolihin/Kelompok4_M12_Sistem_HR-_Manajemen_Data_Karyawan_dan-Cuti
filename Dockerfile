FROM php:8.4-cli

# Install system dependencies, PostgreSQL library, and Node.js
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy project files
COPY . .

# Pastikan file dev Vite (public/hot) tidak terbawa ke production
RUN rm -f public/hot

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Build frontend assets (Tailwind / Vite)
RUN npm install && npm run build

# Set permissions for storage & cache
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000

# Jalankan migrasi otomatis lalu nyalakan server Laravel di port Render ($PORT)
CMD sh -c "php artisan config:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"

