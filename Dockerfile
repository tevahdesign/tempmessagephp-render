# Use official PHP Apache image
FROM php:8.2-apache

# Copy all project files into web root
COPY . /var/www/html/

# Enable Apache rewrite module (useful for SEO URLs)
RUN a2enmod rewrite

# Set correct permissions for Apache
RUN chown -R www-data:www-data /var/www/html

# Expose default web port
EXPOSE 80

# Start Apache web server
CMD ["apache2-foreground"]
