FROM php:8.2-cli

# Системные пакеты
RUN apt-get update && apt-get install -y \
    unzip git curl libzip-dev zip libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Symfony CLI (опционально)
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Рабочая директория
WORKDIR /app

# Копируем проект
COPY . /app

# Устанавливаем зависимости
RUN composer install --no-dev --optimize-autoloader

# Render предоставляет порт через переменную
ENV PORT 10000
EXPOSE $PORT

# Запуск Symfony сервера при старте (с очисткой кеша)
CMD ["sh", "-c", "php bin/console cache:clear --env=prod && php -S 0.0.0.0:$PORT -t public"]
