FROM composer:1.9

COPY . /tools

WORKDIR /tools

RUN composer install --ignore-platform-reqs --no-interaction --optimize-autoloader --no-suggest --no-progress \
    && ln -s /tools/vendor/bin/ecs /bin/ecs \
    && ln -s /tools/vendor/bin/phpstan /bin/phpstan \
    && ln -s /tools/vendor/bin/psalm /bin/psalm \
    && ln -s /tools/vendor/bin/phpinsights /bin/phpinsights
