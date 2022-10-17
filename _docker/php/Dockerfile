FROM php:8.1-fpm
  
RUN apt-get update
RUN apt-get install -y \
        libzip-dev \
        zip \
        libpng-dev \
  && docker-php-ext-install zip \
  && docker-php-ext-install gd

  # Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer