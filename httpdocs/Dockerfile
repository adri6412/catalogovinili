FROM php:8.2-apache

# Installa le estensioni PHP necessarie
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Abilita mod_rewrite per Apache
RUN a2enmod rewrite

# Copia i file dell'applicazione
COPY . /var/www/html/

# Imposta i permessi corretti
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Espone la porta 80
EXPOSE 80

# Avvia Apache
CMD ["apache2-foreground"]

