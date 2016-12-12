FROM php:${php.version}-fpm

MAINTAINER Joomla! Automated Virtualisation Working Group

RUN apt-get update \
    && apt-get install -y \
        libpqxx-dev \
        ruby \
        ruby-dev \
        build-essential \
        sqlite3 \
        libsqlite3-dev

# Setup the Xdebug version to install
ENV XDEBUG_VERSION ${xdebug.version}
ENV XDEBUG_HASH ${xdebug.hash}

# Install Xdebug
RUN set -x \
    && curl -SL "http://www.xdebug.org/files/xdebug-$XDEBUG_VERSION.tgz" -o xdebug.tgz \
    && echo $XDEBUG_HASH xdebug.tgz | ${xdebug.hashtype}sum -c - \
    && mkdir -p /usr/src/xdebug \
    && tar -xf xdebug.tgz -C /usr/src/xdebug --strip-components=1 \
    && rm xdebug.* \
    && cd /usr/src/xdebug \
    && phpize \
    && ./configure \
    && make -j"$(nproc)" \
    && make install \
    && make clean

COPY php.ini /usr/local/etc/php/
COPY conf.d/* /usr/local/etc/php/conf.d/

RUN docker-php-ext-install mysql mysqli \
 && docker-php-ext-install pgsql \
 && docker-php-ext-install pdo_mysql pdo_pgsql


# Cleanup package manager
RUN apt-get remove --purge -y \
        build-essential \
        ruby-dev \
        libsqlite3-dev \
    && apt-get autoclean \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
