FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    zip \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

WORKDIR /app

COPY . /app

RUN composer install --no-dev --optimize-autoloader

RUN php bin/console cache:clear --env=prod

ENV PORT 10000
EXPOSE $PORT

CMD ["sh", "-c", "php -S 0.0.0.0:$PORT -t public"]
