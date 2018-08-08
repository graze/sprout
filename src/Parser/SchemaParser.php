<?php

namespace Graze\Sprout\Parser;

use Graze\Sprout\Config;
use SplFileInfo;

class SchemaParser
{
    /** @var Config */
    private $config;
    /** @var null|string */
    private $group;

    /**
     * SchemaParser constructor.
     *
     * @param Config      $config
     * @param string|null $group
     */
    public function __construct(Config $config, string $group = null)
    {
        $this->config = $config;
        $this->group = $group;
    }

    /**
     * @param array $schemaTables
     *
     * @return ParsedSchema[]
     */
    public function extractSchemas(array $schemaTables = [])
    {
        if (count($schemaTables) === 0) {
            $schemaTables = array_map(
                function (Config\SchemaConfigInterface $schemaConfig) {
                    return $schemaConfig->getSchema();
                },
                $this->config->get(Config::CONFIG_SCHEMAS)
            );
        }

        $parsedSchemas = [];
        foreach ($schemaTables as $schemaTable) {
            if (preg_match('/^([a-z_]+):(.+)$/i', $schemaTable, $matches)) {
                $schema = $matches[1];
                $tables = $matches[2] === '*' ? [] : explode(',', $matches[2]);
            } else {
                $schema = $schemaTable;
                $tables = [];
            }

            $parsedSchemas[] = new ParsedSchema(
                $this->config->getSchemaConfiguration($schema),
                $this->config->getSchemaPath($this->config->getSchemaConfiguration($schema), $this->group),
                $tables
            );
        };

        return array_filter(array_map([$this, 'populateTables'], $parsedSchemas));
    }

    /**
     * @param ParsedSchema $parsedSchema
     *
     * @return ParsedSchema|null
     */
    public function populateTables(ParsedSchema $parsedSchema)
    {
        if (!is_dir($parsedSchema->getPath())) {
            return null;
        }

        if (count($parsedSchema->getTables()) === 0) {
            // find existing tables
            $files = iterator_to_array(new \FilesystemIterator($parsedSchema->getPath()));
            $files = array_values(array_filter(
                $files,
                function (SplFileInfo $file) {
                    // ignore empty file names (`.bla`) files
                    return (!in_array($file->getFilename(), ['.', '..'])
                            && pathinfo($file, PATHINFO_FILENAME) !== '');
                }
            ));

            // sort by file size, largest first
            usort(
                $files,
                function (SplFileInfo $a, SplFileInfo $b) {
                    $left = $a->getSize();
                    $right = $b->getSize();
                    return ($left == $right) ? 0 : (($left > $right) ? -1 : 1);
                }
            );

            // remove the file extensions to get the table names
            $parsedSchema->setTables(array_map(
                function (SplFileInfo $file) {
                    return pathinfo($file, PATHINFO_FILENAME);
                },
                $files
            ));
        }

        if (count($parsedSchema->getTables()) === 0) {
            return null;
        }
        return $parsedSchema;
    }
}
