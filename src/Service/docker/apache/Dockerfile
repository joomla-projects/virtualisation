FROM php:${php.version}-apache

MAINTAINER Joomla! Automated Virtualisation Working Group

COPY php.ini /usr/local/etc/php/
COPY conf.d/* /usr/local/etc/php/conf.d/

RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
		libpq5 \
		libpq-dev \
	&& rm -rf /var/lib/apt/lists/* \
	&& pecl channel-update pecl.php.net \
	&& pecl install xdebug-${xdebug.version} \
	&& docker-php-ext-enable xdebug \
	&& docker-php-ext-install mysqli pdo_mysql \
	&& if [ -f /usr/src/php/ext/mysql ]; then \
		docker-php-ext-install mysql; \
	fi \
	&& docker-php-ext-install pgsql pdo_pgsql \
	&& apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
		libpq-dev
