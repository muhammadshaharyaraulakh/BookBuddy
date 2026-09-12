# Official lightweight PHP 8.2 CLI Alpine image
FROM php:8.2-cli-alpine

# Install system libraries for GD and install pdo_mysql + gd
# Note: curl, mbstring, and fileinfo are already built into php:8.2-cli-alpine by default
RUN apk add --no-cache \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
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

# Expose default port
EXPOSE 8080

# Start PHP built-in server with router.php listening on dynamic $PORT
CMD sh -c "php -S 0.0.0.0:\${PORT:-8080} router.php"
