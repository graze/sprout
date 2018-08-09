# Sprout

[![Latest Version on Packagist](https://img.shields.io/packagist/v/graze/sprout.svg?style=flat-square)](https://packagist.org/packages/graze/sprout)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/graze/sprout/master.svg?style=flat-square)](https://travis-ci.org/graze/sprout)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/graze/sprout.svg?style=flat-square)](https://scrutinizer-ci.com/g/graze/sprout/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/graze/sprout.svg?style=flat-square)](https://scrutinizer-ci.com/g/graze/sprout)
[![Total Downloads](https://img.shields.io/packagist/dt/graze/sprout.svg?style=flat-square)](https://packagist.org/packages/graze/sprout)

Sprout is a tool to help Dump, Truncate and Seed development data into your databases.

![](https://78.media.tumblr.com/534425eb11706448af8ce5838629f76d/tumblr_inline_n9t8gdzC7p1qzjzhu.gif)

1. Seed sql data from local files
1. Dump data from mysql tables 
1. Performs actions in parallel
1. Handle multiple groups of seed data (for example, `static`, `core`, `testing`)

## Install

Via Composer

```bash
~$ composer require graze/sprout
```

Via docker

```bash
`$ docker run -v [volumes] --rm graze/sprout [command]
```

## Usage

### Quick Start

```bash
~$ # Dump all tables you are interested in
~$ sprout dump --config=config/sprout.yml --group=core a_schema:table_1,table_2 ...

~$ # Store the data in your repository of choice
~$ git add /seed/data/*

~$ # Seed the data from your seed data
~$ sprout seed --config=config/sprout.yml --group=core
```

### Seeding

```bash
~$ sprout seed [--config=<path>] [--group=<group>] [--chop] [<schema>[:<table>,...]] ...

~$ sprout seed --config=config/sprout.yml the_schema
~$ sprout seed --config=config/sprout.yml --chop the_schema

~$ sprout seed --config=config/sprout.yml the_schema:country
~$ sprout seed --config=config/sprout.yml --chop the_schema:country other_schema:planets

~$ sprout seed --config=config/sprout.yml --group=core
~$ sprout seed --config=config/sprout.yml --group=core the_schema
~$ sprout seed --config=config/sprout.yml --chop --group=extra
```

### Truncating the data from all the tables in a schema

```bash
~$ sprout chop [--config=<path>] [--group=<group>] [<schema>[:<table>,...]] ...

~$ sprout chop --config=config/sprout.yml the_schema
~$ sprout chop --config=config/sprout.yml the_schema:country

~$ sprout chop --config=config/sprout.yml --group=core the_schema
~$ sprout chop --config=config/sprout.yml --group=extra the_schema:country
```

### Dumping the data from all tables in a schema

```bash
~$ sprout dump [--config=<path>] [--group=<group>] [<schema>[:<table>,...]] ...

~$ sprout dump --config=config/sprout.yml the_schema
~$ sprout dump --config=config/sprout.yml the_schema:country

~$ sprout dump --config=config/sprout.yml --group=core
~$ sprout dump --config=config/sprout.yml --group=core the_schema:country
```

## Testing

```shell
make build test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@graze.com instead of using the issue tracker.

## Credits

- [Harry Bragg](https://github.com/h-bragg)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
