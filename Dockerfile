# Official lightweight PHP 8.2 CLI Alpine image
FROM php:8.2-cli-alpine

# Install essential system libraries & PHP extensions required by BookBuddy
RUN apk add --no-cache \
    curl \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql gd mbstring curl

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

# Start PHP built-in server with router.php listening on Render's dynamic $PORT
CMD sh -c "php -S 0.0.0.0:\${PORT:-8080} router.php"
