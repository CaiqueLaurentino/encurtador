# Usar imagem oficial PHP com Apache
FROM php:8.2-apache

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . /var/www/html

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && composer install --no-dev --optimize-autoloader

RUN mkdir -p /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html/logs

RUN chown -R www-data:www-data /var/www/html/public /var/www/html/src

EXPOSE 80

USER www-data

CMD ["apache2-foreground"]
