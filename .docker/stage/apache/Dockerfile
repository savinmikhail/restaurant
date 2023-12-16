FROM php:8.1-apache

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install required packages
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    libicu-dev \
    git \
    unzip \
    libzip-dev \
    zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

# Install GD extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Install cURL extension
RUN docker-php-ext-install -j$(nproc) curl

# Install PDO MySQL extension
RUN docker-php-ext-install -j$(nproc) pdo_mysql

# Install other extensions
RUN docker-php-ext-install -j$(nproc) iconv mysqli mbstring

# Install zip extension
RUN pecl install zip
RUN docker-php-ext-enable zip

# Install xdebug extension
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Other configurations and copying files
COPY server.crt /etc/apache2/ssl/server.crt
COPY server.key /etc/apache2/ssl/server.key
COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf

# Enable Apache modules
RUN a2enmod rewrite
RUN a2enmod ssl

# for code coverage
COPY php.ini /usr/local/etc/php/conf.d/php.ini

# Create a directory
RUN mkdir -p /tmp/phpsess && chmod -R ga+wrx /tmp/phpsess

EXPOSE 80
