FROM php:8.3-cli
RUN apt-get update && apt-get install -y git unzip libpq-dev libzip-dev && docker-php-ext-install pdo_pgsql zip && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
EXPOSE 80
CMD php -S 0.0.0.0:80 -t public