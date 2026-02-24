FROM php:8.2-apache

# Install required system packages and Composer
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    curl \
    unzip \
    && docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    pgsql \
    pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy API files
COPY . /var/www/html/

# Create composer.json and install Cloudinary SDK
RUN cd /var/www/html && \
    echo '{ "require": { "cloudinary/cloudinary_php": "^3.1" } }' > composer.json && \
    composer install --no-dev

EXPOSE 80