# Sprout

[![Latest Version on Packagist](https://img.shields.io/packagist/v/graze/sprout.svg?style=flat-square)](https://packagist.org/packages/graze/sprout)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/graze/sprout/master.svg?style=flat-square)](https://travis-ci.org/graze/sprout)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/graze/sprout.svg?style=flat-square)](https://scrutinizer-ci.com/g/graze/sprout/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/graze/sprout.svg?style=flat-square)](https://scrutinizer-ci.com/g/graze/sprout)
[![Total Downloads](https://img.shields.io/packagist/dt/graze/sprout.svg?style=flat-square)](https://packagist.org/packages/graze/sprout)

Sprout is a tool to help Dump, Truncate and Seed development data into your databases.

![gif](http://25.media.tumblr.com/3af4d6b7e0d3f81d2c4403b756e48763/tumblr_mzr068rjMy1qkiyi1o1_500.gif)

1. Populates MySQL data
1. Performs actions in parallel

>You now have a copy of the files in this repository, in a new git repository with no previous history that can you manipulate and push to other remote repositories.
>
> ## Continuous Integration
>
>Your project should make use of the following remote CI services:
>
> ### [Travis CI](https://travis-ci.org/graze/) - automated testing
>
> 1. Log-in with github
> 1. visit: https://travis-ci.org/profile/graze
> 1. Click `sync with github`
> 1. Enable your project
>
> ### [Scrutinizer CI](https://scrutinizer-ci.com/organizations/graze/repositories) - code quality
>
> 1. Log-in via github
> 1. Click `+ Add Repository`
> 1. Select `graze` as the organisation (ask a graze/@open-source-team member for access)
> 1. Entry the repository name
> 1. Click `Add Repository`
> 1. Click on the 🔧  > `Configuration` set `Shared Config` to `graze/standards + open source`
>
> ### [Packagist](https://packagist.org/graze) - package repository
>
> 1. Log-in using the graze account
> 1. Click `Submit`
> 1. Paste the `git` url (e.g. `git@github.com:graze/sprout.git`)
> 1. Click `Check`
> 1. Follow the instructions on auto updating the project in packagist
>
> ## Github Teams
>
> Add this project to the graze [Open Source](https://github.com/orgs/graze/teams/open-source-team/members) team to allows others to contribute to this project


## Install

Via Composer

```bash
composer require graze/sprout
```

## Usage

### Initial Start

```bash
~$ # Dump all tables you are interested in
~$ sprout dump --config=config/sprout.yml a_schema table_1 table_2 ...

~$ # Store the 
```

### Seeding

```bash
`$ sprout seed [--config=<path>] [--chop] <schema> [<table>]...

~$ sprout seed --config=config/sprout.yml the_schema
~$ sprout seed --config=config/sprout.yml --chop the_schema

~$ sprout seed --config=config/sprout.yml the_schema country
~$ sprout seed --config=config/sprout.yml --chop the_schema country
```

### Truncating the data from all the tables in a schema

```bash
~$ sprout chop [--config=<path>] <schema> [<table>]...

~$ sprout chop --config=config/sprout.yml the_schema
~$ sprout chop --config=config/sprout.yml the_schema country
```

### Dumping the data from all tables in a schema

```bash
~$ sprout dump [--config=<path>] <schema> [<table>]...

~$ sprout dump --config=config/sprout.yml the_schema
~$ sprout dump --config=config/sprout.yml the_schema country
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
