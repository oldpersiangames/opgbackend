FROM webdevops/php-nginx:8.3
ENV WEB_DOCUMENT_ROOT=/app/public

# Use the default production configuration
# RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN apt-get --allow-insecure-repositories update && apt-get install -y mariadb-client
RUN apt clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .

# RUN chown -R www-data:www-data ./

# Change current user to www
# USER www-data

RUN composer install --optimize-autoloader --no-dev
# RUN php artisan config:cache
# RUN php artisan route:cache
# RUN php artisan optimize
RUN chown -R application:application .

# Expose port 9000 and start php-fpm server
# EXPOSE 9000
# CMD ["php-fpm"]

RUN rm -rf /app/storage/app/public
RUN ln -s /opgactions/opg-backups/public /app/storage/app/public
RUN php artisan storage:link
