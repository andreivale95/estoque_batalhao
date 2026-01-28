# Multi-stage build para Railway
FROM php:8.3-apache

# Set working directory
WORKDIR /app

# Install Node.js and system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    git \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP dependencies
RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libbz2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pdo_mysql \
    xml \
    zip \
    bcmath \
    gd \
    bz2 \
    openssl \
    soap \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

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

# Enable Apache rewrite module
RUN a2enmod rewrite

# Configure Apache for Laravel
RUN echo '<Directory /app/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/laravel.conf && \
    a2enconf laravel

# Set Apache document root
RUN sed -i 's|/var/www/html|/app/public|g' /etc/apache2/sites-available/000-default.conf

# Cache configuration will be done in Procfile release phase
EXPOSE 80

# Default to running Apache
CMD ["apache2-foreground"]
