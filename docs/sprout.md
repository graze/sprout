# Sprout

Sprout is a tool to help you Populate your databases with seed data.

It's core method is by using .sql files that can be committed and diffed by your code repository.

It can:

1. [Seed](seeding.md) sql data from local files
1. [Dump](dumping.md) data from mysql tables
1. Performs actions in parallel
1. Handle multiple [groups](grouping.md) of seed data (for example, `static`, `core`, `testing`)

## Quick Start

### Getting Sprout

You can get sprout in the following ways:

1. [composer](setup/composer.md) to grab the application using PHP's composer
1. [docker](setup/docker.md) to run sprout without installing it locally

### Configuration

You will need a [configuration](setup/configuration.md) file to tell sprout how to talk to your databases.

### Populate your seed data

You can [group](grouping.md) your seed data depending on its purpose. For example: `static`, `operational` and `development`.

Ensure your database is populated with your seed data and run the following command:

```bash
sprout dump --config=/path/to/config.yml --group=group1 schema:table1,table2,... schema2:table3,... ...
```

!!! warning
    Currently you need to create the directories before running this command (or let it fail and tell you what you need
    to create)

### Seed your data

You can now seed your data using the local files.

```bash
sprout seed --config=/path/to/config.yml --group=group1
```
