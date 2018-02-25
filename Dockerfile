FROM php:7-alpine

RUN adduser -h /home/slimreactor -D slimreactor && \
    apk update && apk add nginx supervisor git && \
    mkdir /run/nginx && mkdir -p /var/log/supervisord && \
    cd /home/slimreactor && git clone https://github.com/crazy-goat/slim-reactor.git . && \
    wget https://getcomposer.org/installer  -O - -q | php -- && \
    php composer.phar install -o --no-dev && \
    chown -Rv slimreactor:slimreactor /home/slimreactor

ADD docker/config/supervisord/slim-reactor.ini /etc/supervisor.d/slim-reactor.ini
ADD docker/config/supervisord/nginx.ini /etc/supervisor.d/nginx.ini
ADD docker/config/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
ENTRYPOINT ["supervisord", "-n"]
