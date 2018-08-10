# Schemas and Tables

All of the commands take a standardised set of schemas and tables that is used to build up what tables the action should
be performed on.

The definition looks like:

```text
[<schema>[:<table>,...]] ...
```

This means that it supports multiple optional schemas. Each schema supports an optional list of one or more tables.

## Not specifying anything

If you do not specify anything for this option.

1. Sprout will look at the current schemas defined in your configuration file.
1. Filter out any schemas that do not have a directory in the current group.
1. Find all the tables for each schema.

### Schema scanning example

Given the directory structure:

```text
/seed
- core
  - schema1
    - table1
    - table2
  - schema2
    - table3
- extra
  - schema1
    - table4
```

An empty schema `sprout <action>` will produce the equivalent of:

```text
schema1:table1,table2 schema2:table3
```

If you specified `sprout <action> --group=extra` it would produce the equivalent to:

```text
schema1:table4
```

## Just specifying the schema

You can specify one or more schemas. This will look at all the tables that are defined for only the specified schemas.

The schema values specified should be the names of the schemas defined in the configuration file.
This does not necessary need to match the name of the actual schema.

Given the directory structure:

```text
/seed
- core
  - schema1
    - table1
    - table2
  - schema2
    - table3
  - schema3
    - table5
- extra
  - schema1
    - table4
```

The command:

```bash
sprout <action> schema1 schema2
```

Will be the equivalent of:

```bash
sprout <action> schema1:table1,table2 schema2:table3
```

### Specifying a schema that does not exist locally

If you specify a schema (without any tables) that does not exist in the file structure, it will be ignored.

```bash
sprout <action> --group=extra schema1 schema2
```

Would be the equivalent to:

```bash
sprout <action> --group=extra schema1:table4
```

## Schemas with Tables

You are able to specify a schema with a collection of tables. This will not look at the filesystem to see if they exist
or not, which allows you to dump schemas and tables that do not exist yet.

```bash
sprout <action> schema1:table1,table2
```

Any number of schemas and table combinations can be used

```bash
sprout <action> schema1:table1,table2 schema2:table3 schema3
```
