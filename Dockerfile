FROM php:8.2-apache

# Install ONLY PostgreSQL driver
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy API files
COPY . /var/www/html/

# Initialize composer and install Cloudinary
RUN cd /var/www/html && \
    composer init --name=capstone/app --description="Capstone API" --no-interaction && \
    composer require cloudinary/cloudinary_php

EXPOSE 80