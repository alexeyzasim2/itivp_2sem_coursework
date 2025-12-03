FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite headers

WORKDIR /var/www/html

COPY ./backend /var/www/html/backend
COPY ./presentation/frontend /var/www/html/frontend

RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/override.conf && \
    a2enconf override

