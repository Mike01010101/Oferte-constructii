# --- STAGE 1: Builder ---
# Aici instalam dependentele PHP si Node.js, si construim asset-urile
FROM php:8.2-fpm as builder
ARG APP_URL
ENV APP_URL=${APP_URL}

# Instalam dependentele de sistem + Node.js
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    curl \
    git \
    nodejs \
    npm \
&& docker-php-ext-install pdo_mysql exif pcntl bcmath gd zip

# Instalam Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Setam directorul de lucru
WORKDIR /var/www

# Copiem fisierele, inclusiv scriptul de asteptare
COPY . .
COPY wait-for-it.sh .

# Instalam dependentele Composer FARA cele de dev
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Instalam dependentele NPM si construim asset-urile pentru productie
RUN npm install && npm run build


# --- STAGE 2: Final Image ---
# Aici cream imaginea finala, care va rula pe server
FROM php:8.2-fpm

# Instalam doar extensiile PHP necesare pentru a rula aplicatia
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
&& docker-php-ext-install pdo_mysql exif pcntl bcmath gd zip

WORKDIR /var/www

# Copiem fisierele din stadiul de "builder"
COPY --from=builder /var/www/public ./public
COPY --from=builder /var/www/vendor ./vendor
COPY --from=builder /var/www/bootstrap ./bootstrap
COPY --from=builder /var/www/resources/views ./resources/views
COPY --from=builder /var/www/app ./app
COPY --from=builder /var/www/config ./config
COPY --from=builder /var/www/database ./database
COPY --from=builder /var/www/routes ./routes
COPY --from=builder /var/www/artisan .
COPY --from=builder /var/www/composer.json .
COPY --from=builder /var/www/composer.lock .

# Creăm link-ul simbolic ÎNAINTE de a seta permisiunile
RUN php artisan storage:link

# Setam permisiunile corecte pentru Laravel
RUN mkdir -p bootstrap/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]