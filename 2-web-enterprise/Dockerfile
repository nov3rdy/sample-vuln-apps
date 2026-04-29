FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
        libpng-dev libjpeg-turbo-dev freetype-dev \
        libxml2-dev oniguruma-dev \
        git unzip mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd dom xml

# V12: Security Misconfiguration — display_errors=on in production-style image
RUN { \
        echo "display_errors = On"; \
        echo "display_startup_errors = On"; \
        echo "error_reporting = E_ALL"; \
        echo "log_errors = On"; \
        echo "html_errors = On"; \
        echo "expose_php = On"; \
    } > /usr/local/etc/php/conf.d/zz-debug.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json ./
RUN composer install --no-interaction --no-progress --prefer-dist --no-security-blocking

COPY . .

RUN mkdir -p public/uploads && chown -R www-data:www-data public/uploads

EXPOSE 9000
CMD ["php-fpm"]
