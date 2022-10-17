FROM php:8.1-fpm
  
RUN apt-get update
RUN apt-get install -y \
        libzip-dev \
        zip \
  && docker-php-ext-install zip

  # Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer