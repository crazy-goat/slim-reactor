FROM php:7-alpine

RUN adduser -h /home/slimreactor -D slimreactor && \
    apk update && apk add nginx supervisor && \
    mkdir /run/nginx

ADD examples/config/supervisord/slim-reactor.ini /etc/supervisor.d/slim-reactor.ini
ADD examples/config/supervisord/nginx.ini /etc/supervisor.d/nginx.ini
ADD examples/config/nginx/default.conf /etc/nginx/conf.d/default.conf
