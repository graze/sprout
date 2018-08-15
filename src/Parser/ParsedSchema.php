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

namespace Graze\Sprout\Parser;

use Graze\Sprout\Config\SchemaConfigInterface;

class ParsedSchema
{
    /** @var SchemaConfigInterface */
    private $schemaConfig;
    /** @var string */
    private $path;
    /** @var string[] */
    private $tables;

    /**
     * ParsedSchema constructor.
     *
     * @param SchemaConfigInterface $schemaConfig
     * @param string                $path
     * @param string[]              $tables
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
     * @return string[]
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    /**
     * @param string[] $tables
     *
     * @return ParsedSchema
     */
    public function setTables(array $tables): ParsedSchema
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
