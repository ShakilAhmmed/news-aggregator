FROM php:8.3-fpm

ARG user
ARG uid
# Install system dependencies first
RUN apt-get update && apt-get install -y \
    git curl wget unzip zip libpng-dev libonig-dev libxml2-dev libzip-dev \
    zlib1g-dev libjpeg62-turbo-dev libfreetype6-dev libxrender1 xfonts-75dpi xfonts-base mariadb-client \
    libicu-dev libxslt1-dev libcurl4-openssl-dev pkg-config \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

#Configure GD library
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql intl mbstring exif pcntl bcmath gd zip opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user
RUN useradd -G www-data,root -u $uid -d /home/$user $user \
 && mkdir -p /home/$user/.composer \
 && chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www
RUN chown -R www-data:www-data /var/www
USER $user
