# Use official PHP 8.2 Apache image
FROM php:8.2-apache

# Install PostgreSQL driver + git + unzip + other PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    libcurl4-openssl-dev \
    libonig-dev \
    libssl-dev \
    && docker-php-ext-install pdo_pgsql \
    && docker-php-ext-install curl mbstring \
    && docker-php-ext-enable curl mbstring \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy only composer.json first for caching
COPY composer.json ./

# Install PHP dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Copy the rest of the app
COPY . .

# Ultra simple CMD - forces Render's port (10000) and all interfaces
CMD bash -c 'PORT=${PORT:-10000}; echo "Listen ${PORT}" > /etc/apache2/ports.conf; sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf; echo "ServerName localhost" >> /etc/apache2/apache2.conf; apache2-foreground'