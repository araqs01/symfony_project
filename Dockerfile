# Используем PHP 8.2 CLI
FROM php:8.2-cli

# Устанавливаем системные пакеты и PHP-расширения
RUN apt-get update && apt-get install -y \
    unzip git curl libzip-dev zip libonig-dev libxml2-dev libicu-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Symfony CLI (опционально, можно удалить если не нужен)
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Рабочая директория
WORKDIR /app

# Копируем весь проект
COPY . /app

# Устанавливаем зависимости
RUN composer install --no-dev --optimize-autoloader

# Порт от Render
ENV PORT 10000
EXPOSE $PORT

# CMD: очистка кеша без подключения к БД + запуск встроенного сервера
CMD ["sh", "-c", "php -S 0.0.0.0:$PORT -t public"]
