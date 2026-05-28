FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable mod_rewrite and allow .htaccess overrides
RUN a2enmod rewrite && \
    sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Enable output buffering so session_start() works even with BOM in files
RUN echo 'output_buffering = On' > /usr/local/etc/php/conf.d/custom.ini

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80