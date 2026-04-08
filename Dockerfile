FROM php:8.2-apache

# Install dependencies and PHP extensions
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

# Copy composer files first
COPY composer.json ./
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Copy the rest of the app
COPY . .

# Simple CMD - forces correct port binding for Render
CMD bash -c "echo 'Listen ${PORT:-10000}' > /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \\*:80>/<VirtualHost \\*:${PORT:-10000}>/g' /etc/apache2/sites-available/000-default.conf && \
    echo 'ServerName localhost' >> /etc/apache2/apache2.conf && \
    apache2-foreground"