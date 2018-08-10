# Truncating Data (Chop)

The chop command has the following structure:

```bash
sprout chop [--config=<path>] [--group=<group>] [<schema>[:<table>,...]] ...
```

This will truncate the contents of all the specified tables.

!!! note
    By default when you [seed](seed.md) data it will truncate the tables it finds first, so there is no need to do this
    manually.

## Configuration file

The optional `--config` option allows you specify the configuration file to use. By default it will look for a file
called `config/sprout.yml`. The location is relative to the working directory. Within docker this is `/app`.

## Schema and Table configuration

All commands make use of the same [Schema and Tables](../schemas_tables.md) parsing.

The `[<schema>[:<table>,...]] ...` part of the command line allows you to specify none or some schemas, each schema with
a set of tables or not.

If no schema is defined, all the schemas and tables that are on the filesystem in a group will be truncated.
If no tables are defined for a schema, all tables on the filesystem will be truncated.

### Chopping all the data

You can truncate all the tables that exist locally if you do not specify and schemas or tables.

```bash
sprout chop
```

This will truncate all the data in the default group. See [groups](#groups) for more information on how the groups work.

### Truncating all the tables in a schema

```bash
sprout chop schema1
```

This will chop (truncate) all the tables that exist on the filesystem in the schema: `schema1`.

You can truncate multiple schemas too:

```bash
sprout chop schema1 schema2
```

### Chopping specific tables

If you only want to truncate a set of specific tables you can specify them as a comma separated list
after the schema they apply to.

```bash
sprout chop schema1:table1,table2
```

You can also specify multiple schemas, each with their own set of tables

```bash
sprout chop schema1:table1,table2 schema2:table3
```

## Groups

The optional `--group` option allows you to specify which group to read the seed data from. If this is not
supplied it will use the default value as defined in the [configuration file](../setup/configuration.md).

See [Chopping individual groups](../groups.md#you-can-truncate-from-a-group-as-well) for more information on how to
truncate data in groups.

You can see all of the data in a group by calling:

```bash
sprout chop --group=testing
```

Or you can limit it to a set of schemas and tables:

```bash
sprout chop --group=testing schema1 schema2:table1,table2
```
