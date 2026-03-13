FROM dunglas/frankenphp:latest

RUN install-php-extensions \
    pdo_mysql \
    intl \
    zip

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

COPY . .

RUN php bin/console cache:clear

EXPOSE 80
