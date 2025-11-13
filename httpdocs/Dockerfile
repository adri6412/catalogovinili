FROM php:8.2-apache

# Installa le estensioni PHP necessarie
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Abilita mod_rewrite
RUN a2enmod rewrite

# Configura Apache
RUN { \
    echo '<Directory /var/www/html/>'; \
    echo '    Options -Indexes +FollowSymLinks'; \
    echo '    AllowOverride All'; \
    echo '    Require all granted'; \
    echo '    DirectoryIndex index.php index.html'; \
    echo '</Directory>'; \
    } > /etc/apache2/conf-available/docker-php.conf \
    && a2enconf docker-php

# Copia i file dell'applicazione
COPY . /var/www/html/

# Imposta i permessi corretti
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Espone la porta 80
EXPOSE 80

# Avvia Apache
CMD ["apache2-foreground"]

