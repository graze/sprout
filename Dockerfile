#FROM composer AS build
#
#WORKDIR /app
#COPY src /app/src
#COPY composer.json /app/composer.json
#COPY composer.lock /app/composer.lock
#
#RUN composer install --no-ansi --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader --prefer-dist

FROM graze/php-alpine:7.2 AS run

RUN set +xe \
    && apk add --no-cache \
        mariadb-client

WORKDIR /app
#COPY --from=build /app/src /app/src
#COPY --from=build /app/vendor /app/vendor
COPY src /app/src
COPY vendor /app/vendor
COPY bin /app/bin

ARG BUILD_DATE
ARG VCS_REF

LABEL org.label-schema.schema-version="1.0" \
    org.label-schema.vendor="graze" \
    org.label-schema.name="morphism" \
    org.label-schema.description="extract, diff, and update databases based on differences" \
    org.label-schema.vcs-url="https://github.com/graze/morphism" \
    org.label-schema.vcs-ref=$VCS_REF \
    org.label-schema.build-date=$BUILD_DATE \
    maintainer="developers@graze.com" \
    license="MIT"

VOLUME ["/app/config", "/seed"]

ENTRYPOINT ["php", "/app/bin/sprout"]
CMD ["list"]
