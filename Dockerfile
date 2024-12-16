FROM php:8.2-cli AS base

WORKDIR /var/www/html

# Install system dependencies and PHP extensions needed for package
RUN apt-get update \
    && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    zip \
    libzip-dev \
    && docker-php-ext-install \
    pdo_pgsql \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the project files
COPY . .

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

FROM base AS testing

# Install testing dependencies
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

CMD ["tail", "-f", "/dev/null"]

RUN git config --global --add safe.directory /var/www/html