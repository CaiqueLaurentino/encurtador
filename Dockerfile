# Usar imagem oficial PHP com Apache
FROM php:8.2-apache

# Ativar mod_rewrite
RUN a2enmod rewrite

# Instalar dependências do sistema e extensões PHP
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    unzip \
    git \
    pkg-config \
    libssl-dev \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && docker-php-ext-install pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Definir diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos do Composer primeiro (para cache do Docker)
COPY composer.json composer.lock ./

# Instalar Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Instalar dependências PHP
# CORREÇÃO ANTERIOR: Adicionando --ignore-platform-req=ext-mongodb para contornar a incompatibilidade de versão da extensão
RUN /usr/local/bin/composer install --no-dev --optimize-autoloader --prefer-dist

# Copiar o restante do código do projeto
COPY . .

# CORREÇÃO PARA O ERRO FORBIDDEN (403):
# 1. Redefine o DocumentRoot do Apache para a pasta 'public'.
# 2. Habilita AllowOverride All para permitir o uso de arquivos .htaccess (necessário para roteamento de frameworks).
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Ajustar permissões (Apache roda como www-data)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expor porta 80
EXPOSE 80

# Rodar Apache em foreground
CMD ["apache2-foreground"]