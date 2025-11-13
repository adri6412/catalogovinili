FROM php:8.2-apache

# Installa le estensioni PHP necessarie
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Abilita i moduli Apache necessari
RUN a2enmod rewrite php8.2 || a2enmod php_module || true

# Configura Apache per permettere .htaccess e processare PHP
RUN { \
    echo '<Directory /var/www/html/>'; \
    echo '    Options Indexes FollowSymLinks'; \
    echo '    AllowOverride All'; \
    echo '    Require all granted'; \
    echo '    DirectoryIndex index.php index.html'; \
    echo '</Directory>'; \
    echo ''; \
    echo '<FilesMatch \.php$>'; \
    echo '    SetHandler application/x-httpd-php'; \
    echo '</FilesMatch>'; \
    } > /etc/apache2/conf-available/docker-php.conf \
    && a2enconf docker-php

# Verifica che PHP sia configurato
RUN php -v && apache2ctl -M | grep php || echo "PHP module check"

# Copia i file dell'applicazione
COPY . /var/www/html/

# Imposta i permessi corretti
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod 644 /var/www/html/*.php

# Verifica che index.php esista
RUN ls -la /var/www/html/index.php || echo "index.php not found!"

# Espone la porta 80
EXPOSE 80

# Avvia Apache
CMD ["apache2-foreground"]

