# Используем PHP 8.2 CLI
FROM php:8.2-cli

# Устанавливаем необходимые системные пакеты
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    zip \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Устанавливаем Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Устанавливаем рабочую директорию проекта
WORKDIR /app

# Даем права на папку /app для текущего пользователя (необязательно)
RUN chown -R www-data:www-data /app

# Копируем файлы проекта (если нужно)
# COPY . /app

# Экспонируем порт (для встроенного Symfony-сервера)
EXPOSE 8000

# По умолчанию запускаем bash
CMD ["bash"]
