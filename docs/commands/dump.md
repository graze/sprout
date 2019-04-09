# Dumping your data

The dump command has the following structure:

```bash
sprout dump [--config=<path>] [--group=<group>] [--format=<format>] [<schema>[:<table>,...]] ...
```

This will dump the contents of a table to a related file in the specified group as a collection of sql insert
statements.

## Output File

This is an example of the output from the dump command:

```sql
INSERT INTO `country`
(`id`, `country_code`, `country_code_iso2`, `country_code_iso3`, `name`, `decimal_point`, `thousands_separator`, `added`, `updated`, `deleted`) VALUES
(1,'AF','AF','AFG','Afghanistan','.',',','2012-09-20 12:43:09','2015-10-07 11:05:58',NULL),
(2,'AX','AX','ALA','Aland Islands','.',',','2012-09-20 12:43:09','2015-10-07 11:05:58',NULL),
(3,'AL','AL','ALB','Albania','.',',','2012-09-20 12:43:09','2015-10-07 11:05:58',NULL),
(4,'DZ','DZ','DZA','Algeria','.',',','2012-09-20 12:43:09','2015-10-07 11:05:58',NULL);
```

It puts each row on a separate line to help with storing the data in your code repository and seeing what has changed
over time.

## Configuration file

The optional `--config` option allows you specify the configuration file to use. By default it will look for a file
called `config/sprout.yml`. The location is relative to the working directory. Within docker this is `/app`.

## Schema and Table configuration

All commands make use of the same [Schema and Tables](../schemas_tables.md) parsing.

The `[<schema>[:<table>,...]] ...` part of the command line allows you to specify none or some schemas, each schema with
a set of tables or not.

If no schema is defined, all the schemas and tables in a group will be dumped.
If no tables are defined for a schema, all tables previously dumped will be used.

### Dumping all the files from a schema

```bash
sprout dump schema1
```

This will over-write all the existing dumps from `schema1` for the default group.

You can dump multiple schemas:

```bash
sprout dump schema1 schema2
```

### Dumping specific tables

If you only want to dump a set of specific tables (most likely scenario) you can specify them as a comma separated list
after the schema they apply to.

```bash
sprout dump schema1:table1,table2
```

You can also specify multiple schemas, each with their own set of tables

```bash
sprout dump schema1:table1,table2 schema2:table3
```

## Groups

The optional `--group` option allows you to specify which group the following dump will belong to. If this is not
supplied it will use the default value as defined in the [configuration file](../setup/configuration.md).

See [Creating grouped seed data](../groups.md#creating-grouped-seed-data) for more information on how to dump data into
groups.

```bash
sprout dump --group=testing schema1 schema2:table1,table2
```
