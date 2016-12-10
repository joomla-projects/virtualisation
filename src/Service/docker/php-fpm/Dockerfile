FROM php:5.6-fpm

MAINTAINER Joomla! Automated Testing Working Group

RUN apt-get update \
    && apt-get install -y \
        libpqxx-dev \
        ruby \
        ruby-dev \
        build-essential \
        sqlite3 \
        libsqlite3-dev

# Install MailCatcher
RUN gem install mailcatcher --no-ri --no-rdoc

# Setup the Xdebug version to install
ENV XDEBUG_VERSION 2.2.7
ENV XDEBUG_MD5 71a6b75885207e79762e1e7aaf5c3993

# Install Xdebug
RUN set -x \
    && curl -SL "http://www.xdebug.org/files/xdebug-$XDEBUG_VERSION.tgz" -o xdebug.tgz \
    && echo $XDEBUG_MD5 xdebug.tgz | md5sum -c - \
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
