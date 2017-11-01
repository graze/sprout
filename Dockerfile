FROM php:7.1-alpine

RUN set +xe \
    && apk add --no-cache \
        mariadb-client

COPY bin /app/bin
COPY src /app/src
COPY vendor /app/vendor

VOLUME ["/seed", "/app/config"]

ENTRYPOINT ["php", "/app/bin/sprout"]
CMD ["list"]
