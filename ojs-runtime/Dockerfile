FROM php:8.3-apache-bookworm

# Official OJS release; keep the release and checksum together when upgrading.
ARG OJS_VERSION=3.5.0-6
ARG OJS_SHA256=f5b85703b06e45cc82e56ef72b0a8f42a2f75237e5204dbd0146d9730a627c97
RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates curl cron libicu-dev libonig-dev libpq-dev libxml2-dev \
    libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && missing='' && for extension in bcmath ftp gd intl mbstring mysqli pdo_mysql pgsql pdo_pgsql zip; do \
      if ! php -r 'exit(extension_loaded($argv[1]) ? 0 : 1);' "$extension"; then missing="$missing $extension"; fi; \
    done \
 && if [ -n "$missing" ]; then docker-php-ext-install -j2 $missing; fi \
 && (a2dismod -f mpm_event mpm_worker || true) \
 && a2enmod mpm_prefork rewrite headers setenvif \
 && rm -rf /var/lib/apt/lists/*
RUN curl --fail --show-error --silent --location --retry 3 --proto '=https' \
      "https://pkp.sfu.ca/ojs/download/ojs-${OJS_VERSION}.tar.gz" -o /tmp/ojs.tar.gz \
 && echo "${OJS_SHA256}  /tmp/ojs.tar.gz" | sha256sum -c - \
 && tar -xzf /tmp/ojs.tar.gz --strip-components=1 -C /var/www/html \
 && mkdir -p /opt/fronstellar \
 && mv /var/www/html/public /opt/fronstellar/public.bundled \
 && ln -s /var/ojs/public /var/www/html/public \
 && rm /tmp/ojs.tar.gz \
 && php -r 'foreach (["bcmath","curl","dom","fileinfo","ftp","gd","intl","mbstring","openssl","pdo_pgsql","simplexml","xml","xmlwriter","zip"] as $e) {if (!extension_loaded($e)) {fwrite(STDERR,"Missing PHP extension: $e\n"); exit(1);}}' \
 && chown -R www-data:www-data /var/www/html
COPY php.ini /usr/local/etc/php/conf.d/fronstellar.ini
COPY configure.php /opt/fronstellar/configure.php
COPY start.sh /usr/local/bin/fronstellar-start
RUN chmod 0755 /usr/local/bin/fronstellar-start \
 && php -l /opt/fronstellar/configure.php
WORKDIR /var/www/html
EXPOSE 8080
CMD ["/usr/local/bin/fronstellar-start"]
