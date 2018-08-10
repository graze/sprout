# Sprout Configuration File

The configuration file follows the following standards.

By default sprout looks for a `config/sprout.yml` file, you can specify a different file
using `--config=path/to/file.yml`. This file is relative to the current working directory.

## Complete Configuration File Example

```yaml
# [optional] default properties to use
defaults:

  # [optional, string, default: `core`] The default group to use if `--group` is not specified
  group: core

  # [optional, string, default: `/seed`] The root path of all the seed data
  path: /seed

  # [optional, int, default: `10`] Maximum number of simultaneous processes to run at a time
  simultaneousProcesses: 10

# [optional] The groups root collection allows you to specify custom paths for an individual group
groups:

  # The name of the group (used when specifying `--group=<group>`
  core:

    # [optional, string] A custom path to the group if it does not follow the `/root/group/` hierarchy. This is absolute
    # or relative to the working directory
    path: /custom/path/to/group

# [required, min: 1] Schemas specify each schema you wish to seed in the database and their connection information
schemas:

  # name of the schema to reference. There must be at least 1 schema in the configuration file
  schema1:

    # [optional, string] the actual name of the schema in the database. If not specified, the schema name from
    # above will be used
    schema: 'schema'

    # [optional, string] A custom directory name for this schema, only required if it is different from the `schema` value
    dirName: directory

    # [required] Connection details
    connection: &default_connection

      # [required, string] Username of the database connection
      user: 'morphism'

      # [required, string] Password of the database connection
      password: 'morphism'

      # [required] driver for the database connection, currently only: `mysql` is supported
      driver: 'mysql'

      # [required, string] database on a remote host
      host: 'db'

      # [optional, string] name of the database. If not specified `<schema>` will be used
      dbName: 'schema'

      # [optional, int, default: `3306`] The port to connect on
      port: 3306

  # A second schema,
  schema2:
    connection:
      # you can use yaml anchors to reduce duplicate data
      <<: *default_connection
```

## Minimal Configuration File

This is a minimal configuration file with a single schema and connection

```yaml
schemas:
  first_schema:
    connection: &connection
      host: db
      driver: mysql
      user: root
      password: rootpassword
```
