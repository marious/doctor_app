FROM php:8.5-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libzip-dev \
    libpq-dev \
    libsqlite3-dev \
    libicu-dev \
    && docker-php-ext-configure gd \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    bcmath \
    gd \
    zip \
    intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/laravel

# Copy project files
COPY . .

# Fix git safe directory
RUN git config --global --add safe.directory /var/www/laravel

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/laravel \
    && chmod -R 755 /var/www/laravel/storage

EXPOSE 9000

CMD ["php-fpm"]