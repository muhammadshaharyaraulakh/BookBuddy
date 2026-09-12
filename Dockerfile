# Official lightweight PHP 8.2 CLI Alpine image
FROM php:8.2-cli-alpine

# Install system libraries for GD, cURL, and MySQL
RUN apk add --no-cache \
    curl \
    curl-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql gd

# Install Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy all project code into container
COPY . .

# Install PHP dependencies (production, optimized autoloader)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Ensure entrypoint script is executable
RUN chmod +x /var/www/html/docker-entrypoint.sh

# Expose default port
EXPOSE 8080

# Execute entrypoint script safely using sh
CMD ["sh", "/var/www/html/docker-entrypoint.sh"]
