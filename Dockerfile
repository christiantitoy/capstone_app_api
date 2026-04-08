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

# Enable Apache modules during build
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

# ==================== FIXED CMD FOR RENDER ====================
CMD bash -c '
    # Force Apache to listen ONLY on the correct Render port (default 10000)
    echo "Listen ${PORT:-10000}" > /etc/apache2/ports.conf

    # Create a clean VirtualHost configuration
    cat > /etc/apache2/sites-available/000-default.conf <<EOL
<VirtualHost *:${PORT:-10000}>
    ServerName localhost
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOL

    # Enable the site
    a2ensite 000-default.conf

    # Suppress ServerName warning
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

    # Start Apache in foreground
    apache2-foreground
'