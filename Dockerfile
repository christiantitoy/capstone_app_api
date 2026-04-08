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

# Install PHP dependencies (composer.lock will be created automatically)
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Copy the rest of the app
COPY . .

CMD bash -c "sed -i 's/Listen 80/Listen \$PORT/' /etc/apache2/ports.conf && \
cat <<EOF > /etc/apache2/sites-enabled/000-default.conf
<VirtualHost *:\$PORT>
    DocumentRoot /var/www/html
</VirtualHost>
EOF
apache2-foreground"