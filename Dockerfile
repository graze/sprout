FROM alpine as build-c

WORKDIR /app
COPY dump-parser /app

RUN set +xe \
    && apk add -u --no-cache --virtual .build-deps \
        gcc \
        musl-dev \
    && gcc -O2 -Wall -pedantic process-mysqldump.c -o process-mysqldump \
    && chmod +x process-mysqldump \
    && apk del .build-deps

FROM composer AS build-php

WORKDIR /app
COPY src /app/src
COPY composer.json /app/composer.json
COPY composer.lock /app/composer.lock

RUN composer install --no-ansi --no-dev --no-interaction --no-suggest --no-progress --no-scripts --optimize-autoloader --prefer-dist

FROM graze/php-alpine:7.3 AS run

RUN set +xe \
    && apk add --no-cache \
        mariadb-client

WORKDIR /app
COPY --from=build-php /app/src /app/src
COPY --from=build-php /app/vendor /app/vendor
COPY bin /app/bin
COPY --from=build-c /app/process-mysqldump /bin/process-mysqldump

ARG BUILD_DATE
ARG VCS_REF

LABEL org.label-schema.schema-version="1.0" \
    org.label-schema.vendor="graze" \
    org.label-schema.name="morphism" \
    org.label-schema.description="seed your databases" \
    org.label-schema.vcs-url="https://github.com/graze/sprout" \
    org.label-schema.vcs-ref=$VCS_REF \
    org.label-schema.build-date=$BUILD_DATE \
    maintainer="developers@graze.com" \
    license="MIT"

VOLUME ["/app/config", "/seed"]

ENTRYPOINT ["php", "/app/bin/sprout"]
CMD ["list"]
