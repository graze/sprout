<?php
/**
 * This file is part of graze/sprout.
 *
 * Copyright © 2018 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/sprout/blob/master/LICENSE.md
 * @link    https://github.com/graze/sprout
 */

namespace Graze\Sprout\Db\Parser;

use Graze\Sprout\Config\Config;
use Graze\Sprout\Config\SchemaConfigInterface;
use Graze\Sprout\Db\Schema;
use Graze\Sprout\TablePopulatorInterface;

class SchemaParser
{
    /** @var Config */
    private $config;
    /** @var null|string */
    private $group;
    /** @var TablePopulatorInterface[] */
    private $populators;

    /**
     * SchemaParser constructor.
     *
     * @param Config                  $config
     * @param string|null             $group
     * @param TablePopulatorInterface ...$populators
     */
    public function __construct(Config $config, string $group, TablePopulatorInterface ...$populators)
    {
        $this->config = $config;
        $this->group = $group;
        $this->populators = $populators;
    }

    /**
     * @param array $schemaTables
     *
     * @return Schema[]
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

            $parsedSchemas[] = new Schema(
                $this->config->getSchemaConfiguration($schema),
                $this->config->getSchemaPath($this->config->getSchemaConfiguration($schema), $this->group),
                $tables
            );
        };

        foreach ($parsedSchemas as $schema) {
            foreach ($this->populators as $populator) {
                if ($schema !== null) {
                    $schema = $populator->populateTables($schema);
                }
            }
        }
        // ensure there are no null schemas
        return array_filter($parsedSchemas);
    }
}
