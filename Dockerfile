FROM php:7.4-fpm
RUN apt-get update

ENV PHP_EXTRA_CONFIGURE_ARGS --enable-fpm --with-fpm-user=www-data --with-fpm-group=www-data --disable-cgi
ENV TZ="Europe/Amsterdam"

RUN apt-get update
RUN apt-get install libonig-dev
RUN docker-php-ext-configure pdo
RUN docker-php-ext-configure iconv
RUN docker-php-ext-configure ctype

RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis

#RUN docker-php-ext-configure intl
#RUN docker-php-ext-install libonig-dev

USER www-data
COPY . /var/www/html
WORKDIR /var/www/html

CMD ["php-fpm"]
