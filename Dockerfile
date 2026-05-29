FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN a2enmod rewrite && \
    sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

RUN echo 'output_buffering = On' > /usr/local/etc/php/conf.d/custom.ini

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Fix: Point to public folder
RUN sed -i 's|/var/www/html|/var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80