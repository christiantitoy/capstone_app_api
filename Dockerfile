# Use official PHP 8.2 Apache image
FROM php:8.2-apache

# Install required system packages and PHP extensions
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

# Copy composer files first for better caching
COPY composer.json ./
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Copy the rest of the application
COPY . .

# ==================== RELIABLE START COMMAND FOR RENDER ====================
# This forces Apache to listen on ${PORT} (usually 10000) on all interfaces
CMD ["bash", "-c", "\
    echo \"Listen ${PORT:-10000}\" > /etc/apache2/ports.conf && \
    cat > /etc/apache2/sites-available/000-default.conf << 'EOL' && \
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
    a2ensite 000-default.conf && \
    echo \"ServerName localhost\" >> /etc/apache2/apache2.conf && \
    apache2-foreground \
"]