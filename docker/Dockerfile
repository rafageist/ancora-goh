# syntax=docker/dockerfile:1
# Áncora GOH images for the local Docker Compose deployment (#32).
# Build context: the repository root, filtered by .dockerignore (an allowlist).
#
#   web    PHP 8.4 + Apache, serving www/ only, running as www-data on :8080
#   setup  one-off database installer: PHP 8.4 CLI + psql + dev/sql sources
#
# Neither image contains a password: the web container writes its database
# connection file at start from a Docker secret (docker/web/entrypoint.sh).
ARG PHP_VERSION=8.4.27

# ---------------------------------------------------------------- web
FROM php:${PHP_VERSION}-apache-trixie AS web

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends libpq5 libpq-dev; \
    docker-php-ext-install -j"$(nproc)" pgsql; \
    apt-get purge -y --auto-remove libpq-dev; \
    rm -rf /var/lib/apt/lists/*; \
    php -m | grep -qx pgsql

COPY docker/web/php.ini /usr/local/etc/php/conf.d/zz-ancora.ini
COPY docker/web/apache-ancora.conf /etc/apache2/sites-available/ancora.conf

# Unprivileged Apache: listen on 8080 and own only the runtime directories.
# /run/ancora holds the generated connection file (bin/ancoragoh.conf links
# to it), so the application tree itself stays read-only and root-owned.
RUN set -eux; \
    a2dissite 000-default; \
    a2ensite ancora; \
    sed -ri 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf; \
    sed -ri 's/^ServerTokens .*/ServerTokens Prod/; s/^ServerSignature .*/ServerSignature Off/' /etc/apache2/conf-available/security.conf; \
    install -d -o www-data -g www-data -m 0700 /run/ancora; \
    chown -R www-data:www-data /var/run/apache2 /var/lock/apache2 /var/log/apache2; \
    rm -rf /var/www/html

WORKDIR /var/www/ancora
COPY bin/ bin/
COPY control/ control/
COPY lib/ lib/
COPY model/ model/
COPY view/ view/
COPY www/ www/
RUN set -eux; \
    ln -s /run/ancora/ancoragoh.conf bin/ancoragoh.conf; \
    chown -R root:root /var/www/ancora; \
    chmod -R a+rX,go-w /var/www/ancora

COPY --chmod=0755 docker/web/entrypoint.sh /usr/local/bin/ancora-entrypoint

USER www-data
EXPOSE 8080
ENTRYPOINT ["ancora-entrypoint"]
CMD ["apache2-foreground"]

# ---------------------------------------------------------------- setup
FROM php:${PHP_VERSION}-cli-trixie AS setup

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends postgresql-client; \
    rm -rf /var/lib/apt/lists/*

WORKDIR /opt/ancora
COPY dev/sql/ dev/sql/
COPY --chmod=0755 docker/setup/init-db.sh /usr/local/bin/ancora-init-db
RUN chown -R root:root /opt/ancora && chmod -R a+rX,go-w /opt/ancora

USER 65534:65534
ENV HOME=/tmp
CMD ["ancora-init-db"]
