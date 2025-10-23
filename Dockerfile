FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libsqlite3-dev unzip curl && \
    docker-php-ext-install pdo_sqlite && \
    a2enmod rewrite && \
    rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/app/public
RUN sed -ri "s|DocumentRoot /var/www/html|DocumentRoot ${APACHE_DOCUMENT_ROOT}|g" /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html

RUN mkdir -p /var/www/html/app/data && \
    chown -R www-data:www-data /var/www/html/app/data && \
    chmod -R 775 /var/www/html/app/data

CMD ["bash", "-lc", "php /var/www/html/app/scripts/migrate.php && apache2-foreground"]
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

