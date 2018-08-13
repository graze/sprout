# Database seeding library

This is to spec out what we would require?

## Research

- Web/Dispatch/Stats uses mysqldump / mysql to dump and restore the data in .sql files (for speed)
- Payment API uses some custom seed/builder classes to create the data required
- The other php services uses custom Seed data commands to populate data basically using raw database inserts
- Percy uses in-code based seeding of test data
- subscription history uses custom go code for each seed
- token service uses custom go code to populate items in dynamodb
- address uses custom python code to seed some tables
- Web only currently uses static data

## Requirements

- Handle large database dumps (this shouldn't be the case apart from when you are dealing with monolith)
- Be as quick as possible (use as many tricks to speed up seeding as possible)
- Handle at least mysql
- Group seeding into types (core, operational, testing)
  - You could make a custom group for testing a particular problem ?
- When using a .sql file, the table must be truncated before seeding

### Extended requirements

- Single interface to implement both large .sql dumps and dynamic data
- PHP is required when you are dealing with generated id's
- .sql can be used when you are sure the database is clean beforehand

## Base Design

- You have a series of grouped seed data (e.g. core, sample, test)
- You can apply each of the seed data independently
  - For example, you can 'apply' each test data seed at the beginning of each test run (or maybe even each test)
  - test data should be idempotent (can be created and removed without modifying other test data)

## Commands

### Dump a schema or table

```bash
~$ sprout dump [--group=group] schema [table] [table] [...]

~$ sprout dump --group=core web product
~$ sprout dump web
~$ sprout dump --group=test web account order
```

1. If group is not specified, the default `core` is used

### Restore a single or collection of groups

```bash
~$ sprout restore:group [group] [group] [...]

~$ sprout restore:group core functional test
```

1. One or more groups can be used

## Restore a schema or table

```bash
~$ sprout restore:table [--group=group] schema [table] [table] [...]

~$ sprout restore:table --group=core web product
~$ sprout restore:table web
```

1. Restore a specific schema or table within the schema
1. If no group is specified, the default is used

## Truncate tables

```bash
~$ sprout truncate [--group=group] [schema] [table] [table] [...]

~$ sprout truncate --group=core
~$ sprout truncate web
~$ sprout truncate web product ingeredients
```

1. Can truncate a whole group, schema or table within a schema
1. If group is not specified, the default is used

## Configuration

```yaml
default:
  group: core
  # default path
  path: /seed

# ability to specify custom paths for groups
groups:
  core:
    path: /custom/path/to/stuff

schemas:

  # name of the schema in the database
  <name>:

    # Connection details - this is just an example, you may want to specify
    # different properties, e.g. if connecting to a remote server. You are
    # advised to refer to the 'pdo' documentation for further details.
    connection:
      user: 'morphism'
      password: 'morphism'
      driver: 'pdo_mysql'
      dbName: 'schema'
      # database on a remote host
      host: 'db'
      port: 3306
      # local database
      #unix_socket: '/var/lib/mysql/foo.sock'

    # exclude tables is used when dumping/truncating a whole schema
    # handles regex with automatic ^...$ applied to each entry
    exclude:
      - log_.*
      - migrations
```

### Files

```text
- /seed
  - <group>
    - <schema>
      - <table>.sql
```

## Questions

1. transactions (the whole group / per table?)
1. command line sql vs in php queries?
1. if doing this from within php, how can we parallelise -> spawn workers?
1. when you have multiple groups, how to do you handle truncation?
1. When truncating should you truncate all tables in a schema, irrespective of the seed data?
1. When dealing with projects that are not php, why would use

## Future / Scope

1. Should this be limited to sql dumping/restoring only?
    1. Alternative is to use php/yaml/json files too.
        1. sql/yaml/json are similar, need a yaml/json to sql and back converter.

            ```yaml
            schema: some_schema
            table: some_table
            prefix: prf_

            data:
              - id: 123
                name: something
                date: 2018-02-12 16:15:15

              - id: 234
                name: other
                cake: things
            ```
1. Should this handle stored procedures?
1. How should this handle triggers / complex table setup? (prep tables, disable triggers, indexes, etc)
