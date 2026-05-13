# Use the official PHP image with Apache web server
FROM php:8.2-apache

# Install the necessary PHP extensions for PDO and MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite for clean URLs (good for modern apps)
RUN a2enmod rewrite

# Copy all your project files into the Apache web directory
COPY . /var/www/html/

# Expose port 80 to the outside world
EXPOSE 80
