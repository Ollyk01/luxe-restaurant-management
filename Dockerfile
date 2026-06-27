# Use the official PHP Apache image
FROM php:8.2-apache

# Install MySQL extension
RUN docker-php-ext-install mysqli pdo_mysql

# Copy all files to the web server
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80