FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql pgsql pdo_pgsql

COPY . /var/www/html/

EXPOSE 80