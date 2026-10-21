FROM php:8.4-cli

# Dependências de sistema das extensões
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        unzip \
        git \
    && rm -rf /var/lib/apt/lists/*

# Extensões PHP usadas pelo Presenza (Postgres, QR/PDF, zip)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_pgsql pgsql gd bcmath zip opcache

# Composer disponível dentro da imagem
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Código + dependências EMBUTIDOS na imagem — sem bind do fonte: o `up` do
# Coolify roda a partir de diretório que NÃO existe no lado do daemon, então
# o bind `.:/app` chegava como diretório vazio (sem composer.json/artisan).
# Duas etapas p/ aproveitar cache: deps primeiro, código depois.
WORKDIR /presenza
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload --optimize && php artisan package:discover --ansi
