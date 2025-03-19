FROM php:8.4-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Copy all application code
COPY . .

# Install Composer dependencies without running scripts
RUN composer install --no-scripts --no-dev --optimize-autoloader --no-interaction --no-progress

# Install Node.js dependencies
RUN npm cache clean --force && npm install

# Run Composer scripts after copying all files
RUN composer run-script post-autoload-dump

# Fix permissions for local user (grzelak:grzelak)
ARG USER_ID=1000
ARG GROUP_ID=1000
RUN chown -R ${USER_ID}:${GROUP_ID} /var/www

# Expose port 9000 and start PHP-FPM server
EXPOSE 9000
CMD ["php-fpm"]
