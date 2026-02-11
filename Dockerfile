# Build para Railway
FROM php:8.3-cli

WORKDIR /app

# Update and install all dependencies in one efficient layer
RUN apt-get update && apt-get upgrade -y && \
    apt-get install -y --no-install-recommends \
    curl \
    git \
    ca-certificates \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libbz2-dev && \
    rm -rf /var/lib/apt/lists/*

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y --no-install-recommends nodejs && \
    rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pdo_mysql \
    xml \
    zip \
    bcmath \
    gd \
    bz2

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy application files
COPY . /app/

# Install PHP dependencies (production only)
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader

# Build frontend assets
RUN npm ci && npm run build

# Set permissions for Laravel
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

EXPOSE 8080

# Start PHP built-in server
CMD ["sh", "-c", "php artisan storage:link || true; php -S 0.0.0.0:${PORT:-8080} -t public"]
