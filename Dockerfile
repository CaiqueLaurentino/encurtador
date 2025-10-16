FROM php:8.2-cli

WORKDIR /var/www/html

# Instalar dependências do sistema e extensão MongoDB
RUN apt-get update && apt-get install -y \
    libssl-dev \
    pkg-config \
    git \
    unzip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Instalar Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Copiar arquivos do Composer
COPY composer.json ./
# (Opcional) Se você tiver composer.lock
# COPY composer.json composer.lock ./

# Instalar dependências PHP
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction

# Copiar código do projeto
COPY . .

CMD [ "php", "-S", "0.0.0.0:8000", "-t", "public" ]
