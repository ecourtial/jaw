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
        zip \
    ;

RUN cd /usr/local/etc/php/conf.d/ && \
  echo 'memory_limit = 1G' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

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
