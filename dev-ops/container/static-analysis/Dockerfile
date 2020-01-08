FROM composer:1.9

COPY tools /tools

WORKDIR /tools

RUN composer install --ignore-platform-reqs --no-interaction --optimize-autoloader --no-suggest --no-scripts --no-progress \
    && ln -s /tools/vendor/bin/php-cs-fixer /bin/php-cs-fixer \
    && ln -s /tools/vendor/bin/phpstan /bin/phpstan \
    && ln -s /tools/vendor/bin/psalm /bin/psalm