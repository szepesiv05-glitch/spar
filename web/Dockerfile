FROM php:8.2-apache

# Mysql csomag
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Rewrite modul urlekhez
RUN a2enmod rewrite

# Átállítjuk az Apache főmappáját a public-ra
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Apache jogok
RUN chown -R www-data:www-data /var/www/html

# Home dir
WORKDIR /var/www/html/public