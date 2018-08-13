# Seeding Data

The seed command has the following structure:

```bash
sprout seed [--config=<path>] [--no-chop] [--group=<group>] [<schema>[:<table>,...]] ...
```

This will dump the contents of a table to a related file in the specified group as a collection of sql insert
statements.

!!! note
    Currently only `.sql` files are supported when seeding data.

## Configuration file

The optional `--config` option allows you specify the configuration file to use. By default it will look for a file
called `config/sprout.yml`. The location is relative to the working directory. Within docker this is `/app`.

## Prevent truncation

By default the seed command will truncate all the relevant tables. To prevent this you can specify the `--no-chop`
option.

## Schema and Table configuration

All commands make use of the same [Schema and Tables](../schemas_tables.md) parsing.

The `[<schema>[:<table>,...]] ...` part of the command line allows you to specify none or some schemas, each schema with
a set of tables or not.

If no schema is defined, all the schemas and tables in a group will be seeded.
If no tables are defined for a schema, all tables on the filesystem will be seeded.

### Seeding all the data

You can seed all the current data if you do not specify and schemas or tables.

```bash
sprout seed
```

This will seed all the data in the default group. See [groups](#groups) for more information on how the groups work.

### Seeding all the files in a schema

```bash
sprout seed schema1
```

This will chop (truncate) and seed all the tables that exist on the filesystem in the schema: `schema1`.

If you do not wish to truncate the tables first, you can use:

```bash
sprout seed --no-chop schema1
```

You can seed multiple schemas too:

```bash
sprout seed schema1 schema2
```

### Seeding specific tables

If you only want to seed a set of specific tables you can specify them as a comma separated list
after the schema they apply to.

```bash
sprout seed schema1:table1,table2
```

You can also specify multiple schemas, each with their own set of tables

```bash
sprout seed schema1:table1,table2 schema2:table3
```

## Groups

The optional `--group` option allows you to specify which group to read the seed data from. If this is not
supplied it will use the default value as defined in the [configuration file](../setup/configuration.md).

See [Seeding individual groups](../groups.md#seeding-individual-groups) for more information on how to seed data in
groups.

You can see all of the data in a group by calling:

```bash
sprout seed --group=testing
```

Or you can limit it to a set of schemas and tables:

```bash
sprout seed --group=testing schema1 schema2:table1,table2
```
