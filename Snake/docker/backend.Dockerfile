FROM php:8.3-cli-alpine AS build
WORKDIR /src
COPY backend/ ./
RUN set -eux; \
    for f in *.php; do php -l "$f"; done

FROM php:8.3-apache
WORKDIR /var/www/html
RUN a2enmod rewrite
COPY backend/ /var/www/html/
EXPOSE 80
