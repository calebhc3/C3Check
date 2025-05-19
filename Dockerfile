FROM php:8.4-fpm

# Corrige fontes do APT e instala libs essenciais
RUN if [ -f /etc/apt/sources.list ]; then \
        sed -i 's|http://deb.debian.org|https://deb.debian.org|g' /etc/apt/sources.list; \
    fi \
    && if [ -f /etc/apt/sources.list.d/debian.sources ]; then \
        sed -i 's|http://deb.debian.org|https://deb.debian.org|g' /etc/apt/sources.list.d/debian.sources; \
    fi \
    && apt-get update \
    && apt-get install -y \
        libzip-dev \
        libpng-dev \
        libicu-dev \
        default-libmysqlclient-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl zip gd pdo pdo_mysql opcache

# Copia o composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define timezone e encoding
ENV TZ=America/Sao_Paulo
RUN echo "date.timezone=${TZ}" > /usr/local/etc/php/conf.d/timezone.ini

# Define diretório de trabalho
WORKDIR /var/www

# Comando padrão
CMD ["php-fpm"]
