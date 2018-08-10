# Grouping Seed Data

Not all seed data is made equal.

For example you can have:

- Static data: stuff that will not change whatever, types, etc
- Operational data: a minimum subset of data to make the application work
- Testing data: sample data that you can use to test the application with (accounts, products, etc)

Sprout allows you to group your seed data into arbitrary categories.

## Creating grouped seed data

!!! note
    Currently you must create the group/schema directory before running this.

You can dump data from sprout into a grouped collection using the `--group=<group>` option on all of the commands

```bash
sprout dump --group=static schema:table1,table2 schema2:table3 ...
```

This will write the data in `table1` and `table2` from `schema` and `table3` from `schema2` into to `static` group
directory. Resulting in the data structure:

```text
- /seed
  - static
    - schema
      - table1.sql
      - table2.sql
    - schema2
      - table3.sql
```

If you do not specify a set of schemas or tables it will dump all the data from all the schemas and tables previously
dumped.

```bash
sprout dump --group=static
```

### Dumping to a different group

You can then dump your operational or testing data tables using a different group.

```bash
sprout dump --group=operational schema:table4,table5
```

This will result in the data structure:

```text
- /seed
  - operational
    - schema
      - table4.sql
      - table5.sql
  - static
    - schema
      - table1.sql
      - table2.sql
    - schema2
      - table3.sql
```

## Seeding individual groups

You can then just seed an individual group using the same `--groups=<group>` option.

```bash
sprout seed --group=static
```

If you want to only seed a subset of the schemas, or tables you can still specify them in the command line.

```bash
sprout seed --group=static schema1

sprout seed --group=static schema1:table1 schema2
```

## You can truncate from a group as well

!!! warning
    If the same schema and table are in multiple groups, it will truncate the entire table

```bash
sprout chop --group=static
```
