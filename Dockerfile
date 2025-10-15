FROM php:8.2-cli
WORKDIR /app
COPY . .
RUN apt-get update && apt-get install -y libpng-dev libonig-dev libxml2-dev unzip
RUN docker-php-ext-install pdo pdo_mysql
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
