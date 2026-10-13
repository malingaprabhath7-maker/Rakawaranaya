# Rakawarana — single container for a free demo on Render (or any Docker host).
# Stage 1: build the CSS/JS
FROM node:22-alpine AS assets
WORKDIR /app
COPY package*.json vite.config.js ./
COPY resources ./resources
RUN npm install --no-audit --no-fund && npm run build

# Stage 2: PHP + Apache
FROM php:8.3-apache
RUN apt-get update && apt-get install -y --no-install-recommends unzip git libzip-dev \
    && docker-php-ext-install zip opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
    && echo "ServerTokens Prod\nServerSignature Off" > /etc/apache2/conf-enabled/zz-hardening.conf \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "expose_php=Off\nupload_max_filesize=6M\npost_max_size=8M" > "$PHP_INI_DIR/conf.d/zz-app.ini"

WORKDIR /var/www/html
COPY . .
COPY --from=assets /app/public/build ./public/build
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs database \
    && chown -R www-data:www-data storage bootstrap/cache database

COPY docker/start.sh /usr/local/bin/start
RUN chmod +x /usr/local/bin/start
CMD ["start"]
