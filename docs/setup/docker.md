# Running sprout with docker

There is a docker image: `graze/sprout` on [DockerHub](https://hub.docker.com/r/graze/sprout) that can be used to run
sprout.

When specifying paths in the configuration file, remember they are relative to the sprout working directory
(default: `/app`).

You will need to mount your configuration and seed data:

## Command Line

This will run sprout with seed data in the default `/seed` path.

```bash
docker run --rm -v $$(pwd)/config/sprout:/app/config -v $$(pwd)/seed:/seed graze/sprout [command]
```

## Docker Compose

An example `docker-compose.yml` file.

```yaml
version: '2'

services:
  sprout:
    image: graze/sprout
    depends_on:
      - db
    volumes:
      - ./config/sprout:/app/config:cached
      - ./seed:/seed:delegated

  db:
    image: mysql:5
    environment:
      MYSQL_USER: dev
      MYSQL_PASSWORD: password
      MYSQL_DATABASE: the_schema
      MYSQL_ROOT_PASSWORD: rootpassword
```

With this file you can run sprout using the commands:

```bash
docker-compose run --rm sprout [command]
```

## Versions

Different versions can be used by defining tags on the image:

```yaml
services:
  sprout:
    image: graze/sprout:0.1
```

```bash
docker run --rm -v $(pwd)/config/sprout:/app/config -v $(pwd)/seed:/seed graze/sprout:0.1 [command]
```
