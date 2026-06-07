FROM php:8.2-apache

# Enable Apache rewrite
RUN a2enmod rewrite

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy project
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Permissions
RUN chown -R www-data:www-data /var/www/html