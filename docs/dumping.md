# Dumping your data

The dump command has the following structure:

```bash
sprout dump [--config=<path>] [--group=<group>] [<schema>[:<table>,...]] ...
```

This will dump the contents of a table to a related file in the specified group as a collection of sql insert
statements.

## Output File

This is an example of the output from the dump command:

```sql
INSERT INTO `country` (`id`, `country_code`, `country_code_iso2`, `country_code_iso3`, `name`, `decimal_point`, `thousands_separator`, `added`, `updated`, `deleted`) VALUES
(1,'AF','AF','AFG','Afghanistan','.',',','2012-09-20 12:43:09','2015-10-07 11:05:58',NULL),
(2,'AX','AX','ALA','Aland Islands','.',',','2012-09-20 12:43:09','2015-10-07 11:05:58',NULL),
(3,'AL','AL','ALB','Albania','.',',','2012-09-20 12:43:09','2015-10-07 11:05:58',NULL),
(4,'DZ','DZ','DZA','Algeria','.',',','2012-09-20 12:43:09','2015-10-07 11:05:58',NULL);
```

It puts each row on a separate line to help with storing the data in your code repository and seeing what has changed
over time.
