FROM php:8.1-cli

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    unzip \
    curl \
    libzip-dev \
    zip \
    git \
    && docker-php-ext-install pdo pdo_mysql

COPY . .

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && composer install

COPY ./entrypoint.sh /var/www/entrypoint.sh

ENTRYPOINT ["/var/www/entrypoint.sh"]
