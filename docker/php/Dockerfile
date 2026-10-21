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

# Laravel precisa do artisan executável
WORKDIR /app
