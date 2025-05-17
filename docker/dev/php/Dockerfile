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
        zlib1g-dev \
        libzip-dev \
        libicu-dev \
    ;

WORKDIR /var/www/html

RUN set -xe; \
    curl -sl https://getcomposer.org/composer-stable.phar -o /usr/local/bin/composer; \
    chmod +x /usr/local/bin/composer; \

RUN set -xe; \
    docker-php-ext-install -j$(nproc) \
        opcache \
        pdo_mysql \
        zip \
        intl \
    ;

RUN cd /usr/local/etc/php/conf.d/ && \
  echo 'memory_limit = 1G' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

# php-fpm config
ENV PHP_FPM_PM_LOG_LEVEL=warning \
    TIMEZONE=UTC

ARG USER_UID
ARG USER_GID

RUN set -xe; \
    mkdir -p /var/www/html; \
    chown -R ${USER_UID}:${USER_UID} /var/www;
