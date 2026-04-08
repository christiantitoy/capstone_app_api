# Use official PHP 8.2 Apache image
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    libcurl4-openssl-dev \
    libonig-dev \
    libssl-dev \
    && docker-php-ext-install pdo_pgsql curl mbstring \
    && docker-php-ext-enable curl mbstring \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first (better layer caching)
COPY composer.json ./
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Copy the rest of the application
COPY . .

# ==================== SIMPLE & RELIABLE CMD FOR RENDER (fixes 521) ====================
CMD bash -c '
    # Set correct port (Render default is 10000)
    echo "Listen ${PORT:-10000}" > /etc/apache2/ports.conf

    # Overwrite default site config to bind to all interfaces on correct port
    cat > /etc/apache2/sites-available/000-default.conf << "EOL"
<VirtualHost *:'"${PORT:-10000}"'>
    ServerName localhost
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOL

    a2ensite 000-default.conf

    echo "ServerName localhost" >> /etc/apache2/apache2.conf

    # Start Apache
    apache2-foreground
'