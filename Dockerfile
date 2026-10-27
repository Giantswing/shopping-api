# Use FrankenPHP base image
FROM dunglas/frankenphp:1.13.4-php8.3.36-alpine

# Install system dependencies
RUN apk add --no-cache bash git libzip-dev supervisor

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install required PHP extensions for Laravel + Octane
RUN docker-php-ext-install pdo pdo_mysql pcntl zip
RUN echo "session.upload_progress.enabled = Off" > /usr/local/etc/php/conf.d/99-security.ini

# Copy project files
COPY . /app
WORKDIR /app

# Install PHP dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --prefer-dist --no-interaction

# Install Octane with FrankenPHP server
RUN yes | php artisan octane:install --server=frankenphp
ENV OCTANE_SERVER=frankenphp

# Entrypoint
ENTRYPOINT ["sh", "/app/docker/entrypoint.sh"]
