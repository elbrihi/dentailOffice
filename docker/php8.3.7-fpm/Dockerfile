FROM php:8.3-fpm

ARG TIMEZONE=Europe/Paris

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required for Symfony
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    intl \
    opcache

# Install Xdebug (v3)
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Configure Xdebug 3 properly
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/xdebug.ini

# Set timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo ${TIMEZONE} > /etc/timezone \
    && echo "date.timezone=${TIMEZONE}" > /usr/local/etc/php/conf.d/timezone.ini

# Install Composer (official way)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/app