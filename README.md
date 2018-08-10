# Sprout

[![Latest Version on Packagist](https://img.shields.io/packagist/v/graze/sprout.svg?style=flat-square)](https://packagist.org/packages/graze/sprout)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/graze/sprout/master.svg?style=flat-square)](https://travis-ci.org/graze/sprout)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/graze/sprout.svg?style=flat-square)](https://scrutinizer-ci.com/g/graze/sprout/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/graze/sprout.svg?style=flat-square)](https://scrutinizer-ci.com/g/graze/sprout)
[![Total Downloads](https://img.shields.io/packagist/dt/graze/sprout.svg?style=flat-square)](https://packagist.org/packages/graze/sprout)
[![PHP Version](https://img.shields.io/packagist/php-v/graze/sprout.svg?style=flat-square)](https://php.net)
[![Docker Image Size](https://img.shields.io/microbadger/image-size/graze/sprout.svg?style=flat-square)](https://hub.docker.com/r/graze/sprout/)

Sprout is a tool to help Dump, Truncate and Seed development data into your databases.

![](https://78.media.tumblr.com/534425eb11706448af8ce5838629f76d/tumblr_inline_n9t8gdzC7p1qzjzhu.gif)

1. Seed sql data from local files
1. Dump data from mysql tables
1. Performs actions in parallel
1. Handle multiple groups of seed data (for example, `static`, `core`, `testing`)

## Install

Via Composer

```bash
composer require graze/sprout
```

Via docker

```bash
docker run -v [volumes] --rm graze/sprout [command]
```

## Usage

### File Structure

Sprout will use the following file structure by default, you can change the root and each group's path in the 
configuration file.

```text
- /seed
  - group1
    - schema1
      - table1.sql
      - table2.sql
    - schema2
      - table3.sql
  - group1
    - schema3
      - table4.sql
```

### Quick Start

```bash
# Dump all tables you are interested in
sprout dump --config=config/sprout.yml --group=static a_schema:table_1,table_2 ...

# Store the data in your repository of choice
git add /seed/static/*

# Seed the data from your local files
sprout seed --config=config/sprout.yml --group=static
```

### Seeding

```bash
sprout seed [--config=<path>] [--group=<group>] [--chop] [<schema>[:<table>,...]] ...

sprout seed --config=config/sprout.yml the_schema
sprout seed --config=config/sprout.yml --chop the_schema

sprout seed --config=config/sprout.yml the_schema:country
sprout seed --config=config/sprout.yml --chop the_schema:country other_schema:planets

sprout seed --config=config/sprout.yml --group=core
sprout seed --config=config/sprout.yml --group=core the_schema
sprout seed --config=config/sprout.yml --chop --group=extra
```

### Truncating the data from all the tables in a schema

```bash
sprout chop [--config=<path>] [--group=<group>] [<schema>[:<table>,...]] ...

sprout chop --config=config/sprout.yml the_schema
sprout chop --config=config/sprout.yml the_schema:country

sprout chop --config=config/sprout.yml --group=core the_schema
sprout chop --config=config/sprout.yml --group=extra the_schema:country
```

### Dumping the data from all tables in a schema

```bash
sprout dump [--config=<path>] [--group=<group>] [<schema>[:<table>,...]] ...

sprout dump --config=config/sprout.yml the_schema
sprout dump --config=config/sprout.yml the_schema:country

sprout dump --config=config/sprout.yml --group=core
sprout dump --config=config/sprout.yml --group=core the_schema:country
```

### Configuration

The configuration file follows the following standards.

By default sprout looks for a `config/sprout.yml` file, you can specify a different file 
using `--config=path/to/file.yml`. 

```yaml
defaults:
  group: core
  # default path
  path: /seed
  # number of simultaneous processors to run at a time (default: 10)
  simultaneousProcesses: 10

# ability to specify custom paths for groups 
groups:
  core:
    path: /custom/path/to/group

schemas:
  # name of the schema to reference
  <name>:
    # [optional] the actual name of the schema in the database. If not specified, <name> from above will be used
    schema: 'schema'
    
    # Connection details - this is just an example, you may want to specify
    # different properties, e.g. if connecting to a remote server. You are
    # advised to refer to the 'pdo' documentation for further details.
    connection:
      user: 'morphism'
      password: 'morphism'
      # driver for the database connection, currently only: `mysql` is supported
      driver: 'mysql'
      # [optional] name of the database
      dbName: 'schema'
      # database on a remote host
      host: 'db'
      # [optional] port to use, by default: 3306
      port: 3306
```

## Testing

```bash
make build test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@graze.com
instead of using the issue tracker.

## Credits

- [Harry Bragg](https://github.com/h-bragg)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
