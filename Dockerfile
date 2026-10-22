# Five Star Tours — PHP 8.2 + Apache
FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers expires deflate \
    && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf 'upload_max_filesize=6M\npost_max_size=8M\nexpose_php=Off\nsession.cookie_httponly=1\n' > "$PHP_INI_DIR/conf.d/app.ini"

COPY . /var/www/html/
COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint && chown -R www-data:www-data /var/www/html/uploads

EXPOSE 80
ENTRYPOINT ["app-entrypoint"]
CMD ["apache2-foreground"]
