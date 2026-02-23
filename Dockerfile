FROM php:8.2-apache

# Install required system packages
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    pgsql \
    pdo_pgsql

# Copy API files
COPY . /var/www/html/

EXPOSE 80