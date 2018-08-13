<?php

namespace Graze\Sprout\Parser;

use Graze\Sprout\Config\Config;
use Graze\Sprout\Config\SchemaConfigInterface;

class SchemaParser
{
    /** @var Config */
    private $config;
    /** @var null|string */
    private $group;
    /** @var TablePopulator */
    private $populator;

    /**
     * SchemaParser constructor.
     *
     * @param TablePopulator $populator
     * @param Config         $config
     * @param string|null    $group
     */
    public function __construct(TablePopulator $populator, Config $config, string $group = null)
    {
        $this->populator = $populator;
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
                function (SchemaConfigInterface $schemaConfig) {
                    return $schemaConfig->getSchema();
                },
                $this->config->get(Config::CONFIG_SCHEMAS)
            );
        }

        $parsedSchemas = [];
        foreach ($schemaTables as $schemaTable) {
            if (preg_match('/^([a-z0-9_]+):(.+)$/i', $schemaTable, $matches)) {
                $schema = $matches[1];
                $tables = explode(',', $matches[2]);
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

        return array_filter(array_map([$this->populator, 'populateTables'], $parsedSchemas));
    }
}
