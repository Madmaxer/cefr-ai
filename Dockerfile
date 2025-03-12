FROM php:8.4-fpm

# Instalacja zależności
RUN apt-get update && apt-get install -y \
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
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalacja Composera
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Ustawienie katalogu roboczego
WORKDIR /var/www

# Kopiowanie zawartości aplikacji
COPY . /var/www

# Instalacja zależności Composera
RUN composer install

# Udostępnienie portu 9000 i uruchomienie serwera PHP-FPM
EXPOSE 9000
CMD ["php-fpm"]
