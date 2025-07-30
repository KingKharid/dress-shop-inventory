# Use official PHP image with Apache
FROM php:8.2-apache

# Install system dependencies and PostgreSQL driver
RUN apt-get update \
 && apt-get install -y libpq-dev \
 && docker-php-ext-install pdo_pgsql

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html
