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

namespace Graze\Sprout\Db;

use Graze\Sprout\Config\SchemaConfigInterface;

class Schema
{
    /** @var SchemaConfigInterface */
    private $schemaConfig;
    /** @var string */
    private $path;
    /** @var Table[] */
    private $tables;

    /**
     * ParsedSchema constructor.
     *
     * @param SchemaConfigInterface $schemaConfig
     * @param string                $path
     * @param Table[]               $tables
     */
    public function __construct(SchemaConfigInterface $schemaConfig, string $path, array $tables)
    {
        $this->schemaConfig = $schemaConfig;
        $this->path = $path;
        $this->tables = $tables;
    }

    /**
     * @return SchemaConfigInterface
     */
    public function getSchemaConfig(): SchemaConfigInterface
    {
        return $this->schemaConfig;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return Table[]
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    /**
     * @param Table[] $tables
     *
     * @return Schema
     */
    public function setTables(array $tables): Schema
    {
        $this->tables = $tables;
        return $this;
    }

    /**
     * @return string
     */
    public function getSchemaName(): string
    {
        return $this->schemaConfig->getSchema();
    }
}
