FROM php:8.1-fpm

# Install requirement
RUN set -xe; \
    apt-get update; \
    apt-get install -y \
        bash \
        bash-completion \
        curl \
        openssl \
        git \
        make \
        grep \
        jq \
        unzip \
        wget \
        nano \
    ;

WORKDIR /var/www/html

RUN set -xe; \
    curl -sl https://getcomposer.org/composer-stable.phar -o /usr/local/bin/composer; \
    chmod +x /usr/local/bin/composer; \
    curl -Ls https://github.com/fabpot/local-php-security-checker/releases/download/v1.0.0/local-php-security-checker_1.0.0_linux_amd64 -o /usr/local/bin/local-php-security-checker; \
    chmod +x /usr/local/bin/local-php-security-checker;

RUN set -xe; \
    docker-php-ext-install -j$(nproc) \
        opcache \
        pdo_mysql \
    ; \
    pecl install -o xdebug; \
    docker-php-ext-enable xdebug;

ARG XDEBUG_CLIENT_HOST=172.17.0.1
ARG XDEBUG_CLIENT_PORT=9003

# xdebug settings
RUN set -xe; \
    echo "xdebug.start_with_request=yes" >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.discover_client_host=true" >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.client_host="${XDEBUG_CLIENT_HOST} >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.client_port="${XDEBUG_CLIENT_PORT} >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.mode=coverage,develop,debug" >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini;

RUN cd /usr/local/etc/php/conf.d/ && \
  echo 'memory_limit = 512M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

ARG GITHUB_SECRET_TOKEN
ENV COMPOSER_AUTH='{"github-oauth":{"github.com":"'${GITHUB_SECRET_TOKEN}'"}}'

# php-fpm config
ENV PHP_FPM_PM_LOG_LEVEL=warning \
    TIMEZONE=UTC

ARG USER_UID
ARG USER_GID

RUN set -xe; \
    mkdir -p /var/www/html; \
    chown -R ${USER_UID}:${USER_UID} /var/www;
