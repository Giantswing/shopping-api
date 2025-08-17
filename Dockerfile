# Use FrankenPHP as the base image
FROM ghcr.io/frankencms/frankenphp:latest

# Set working directory inside container
WORKDIR /var/www/html

# Copy composer files first (caching layer)
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy the rest of the project
COPY . .

# Make sure storage & cache are writable
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose port for FrankenPHP
EXPOSE 8000

# Start FrankenPHP server
CMD ["frankenphp", "start", "--host", "0.0.0.0", "--port", "8000"]
