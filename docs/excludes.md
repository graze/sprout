# Excludes

The configuration option: `schemas.<schema>.exclude` allows you to specify a list of regular expressions to exclude
from any chop, dump or seed command.

## Configuration

These are defined in the [configuration file](setup/configuration.md).

```yaml
schemas:
  schema:
    excludes:
      - 'table'
      - 'regex*'
      - '/custom_regex/'
```

You can specify a simple string, a regex, or a complex regex.

## Regular Expressions

The regular expressions are surrounded by: `/^<exclude>$/`, so it will match the complete table name if possible.

If you do not want this to apply, place a `/` at the beginning of the table to be excluded.

## Examples

Setting these configuration options will remove the tables from the the list that will be considered.

```yaml
schemas:
  schema:
    excludes:
      - 'table'
      - 'regex.*'
      - '/^table\d{4}/'
```

The above will filter the tables:

| Table           | Filtered |
|-----------------|----------|
| `table`         | yes      |
| `regex`         | yes      |
| `regexsometext` | yes      |
| `aregex`        | no       |
| `table1234`     | yes      |
| `table123`      | no       |
| `table12345`    | yes      |

!!! note
    Tables defined on the command will override the excludes list

    ```bash
    sprout seed schema:table
    ```

    Will seed the `table` file even though it is defined in the excludes list.
