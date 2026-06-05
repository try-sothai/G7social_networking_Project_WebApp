FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install pdo pdo_mysql mysqli gd

RUN a2enmod rewrite && \
    sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

RUN echo 'output_buffering = On' > /usr/local/etc/php/conf.d/custom.ini

COPY . /var/www/html/

RUN mkdir -p /var/www/html/public/uploads/chat \
    && mkdir -p /var/www/html/public/uploads/calls \
    && mkdir -p /var/www/html/public/uploads/avatars \
    && mkdir -p /var/www/html/public/uploads/posts \
    && mkdir -p /var/www/html/public/uploads/stories \
    && mkdir -p /var/www/html/public/uploads/events \
    && mkdir -p /var/www/html/public/uploads/groups \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/public/uploads

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80