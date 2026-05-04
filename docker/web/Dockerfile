FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod ssl rewrite headers \
    && printf "ServerName localhost\nServerTokens Prod\nServerSignature Off\n" > /etc/apache2/conf-available/clo2-security.conf \
    && a2enconf clo2-security \
    && printf "expose_php=Off\n" > /usr/local/etc/php/conf.d/clo2-security.ini \
    && mkdir -p /etc/ssl/localcerts \
    && openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout /etc/ssl/localcerts/clo2.key \
        -out /etc/ssl/localcerts/clo2.crt \
        -subj "/C=ID/ST=Jawa Barat/L=Bandung/O=CLO2/OU=Kamsis/CN=localhost" \
        -addext "subjectAltName=DNS:localhost,IP:127.0.0.1,IP:192.168.1.240"

COPY docker/web/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/web/apache/default-ssl.conf /etc/apache2/sites-available/default-ssl.conf

RUN a2ensite default-ssl

WORKDIR /var/www/html
