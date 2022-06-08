FROM php:8.1-cli
RUN apt-get update && \
    apt-get install -y liblua5.1-0-dev && \
    pecl install luaSandbox \
	&& docker-php-ext-enable luasandbox

ADD . /usr/src/silicon
WORKDIR /usr/src/silicon

CMD [ "php", "./vendor/bin/phpunit" ]