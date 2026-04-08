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

# === FIXED START COMMAND ===
CMD bash -c ' \
    # Enable necessary Apache modules (important for PHP + clean config)
    a2enmod rewrite headers \
    && \
    # Replace default Listen 80 with the Render PORT (usually 10000)
    sed -i "s/Listen 80/Listen ${PORT:-10000}/g" /etc/apache2/ports.conf \
    && \
    # Make sure Apache listens on ALL interfaces (0.0.0.0), not just localhost
    sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT:-10000}>/g" /etc/apache2/sites-available/000-default.conf \
    && \
    # Add ServerName to suppress warnings
    echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && \
    # Start Apache in foreground
    apache2-foreground'