FROM php:8.5-cli

# 1. Install system dependencies
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
    sockets \
    pcntl \
    pdo \
    pdo_mysql \
    bcmath \
    gd \
    zip \
    intl

# 2. Install Node.js & Chokidar (REQUIRED for --watch)
# Install Node.js and Chokidar globally
# 2. Install Node.js & Chokidar
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g chokidar

# 3. FIX: Set the Node Path so Octane can find the global chokidar
ENV NODE_PATH=/usr/lib/node_modules

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/laravel

# Copy project files
COPY . .

# Install dependencies and Octane
RUN composer install \
    && php artisan octane:install --server=roadrunner

# Expose port
EXPOSE 8000

# Start Octane with --watch
CMD ["php", "artisan", "octane:start", "--server=roadrunner", "--host=0.0.0.0", "--port=8000", "--watch"]
