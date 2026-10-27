# Access-request portal (R0) for private staging; the same image can run on a
# host with Docker if the maintainer chooses containers (docs/saas/host-assessment.md).
# Versions match the production host: PHP 8.5, PostgreSQL 18.
FROM composer:2.10.3 AS deps
WORKDIR /app
COPY composer.json composer.lock ./
COPY src ./src
RUN composer install --no-dev --no-interaction --no-progress --classmap-authoritative --ignore-platform-req=ext-pdo_pgsql

FROM php:8.5.2-apache-trixie AS web
RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev postgresql-client \
 && docker-php-ext-install pdo_pgsql \
 && apt-get purge -y libpq-dev && apt-get autoremove -y && rm -rf /var/lib/apt/lists/* \
 && a2dissite 000-default && a2enmod headers \
 && sed -ri 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf \
 && printf 'ServerTokens Prod\nServerSignature Off\n' > /etc/apache2/conf-enabled/zz-security.conf \
 && chown -R www-data:www-data /var/run/apache2 /var/lock/apache2 /var/log/apache2
COPY deploy/docker/php.ini /usr/local/etc/php/conf.d/zz-portal.ini
COPY deploy/docker/portal.conf /etc/apache2/sites-enabled/portal.conf
COPY --chown=root:root src /srv/portal/src
COPY --chown=root:root templates /srv/portal/templates
COPY --chown=root:root public /srv/portal/public
COPY --chown=root:root sql /srv/portal/sql
COPY --chown=root:root bin /srv/portal/bin
COPY --from=deps --chown=root:root /app/vendor /srv/portal/vendor
RUN mkdir -p /outbox && chown www-data:www-data /outbox && chmod 700 /outbox
USER www-data
EXPOSE 8080
HEALTHCHECK --interval=30s --timeout=5s --retries=3 CMD ["php", "-r", "exit(@file_get_contents('http://127.0.0.1:8080/healthz') === \"ok\\n\" ? 0 : 1);"]
