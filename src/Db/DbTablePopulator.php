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

use Graze\Sprout\Parser\ParsedSchema;
use Graze\Sprout\Parser\TableFilterer;
use Graze\Sprout\Parser\TablePopulatorInterface;

class DbTablePopulator implements TablePopulatorInterface
{
    /** @var PdoFactory */
    private $pdoFactory;
    /** @var TableFilterer */
    private $tableFilterer;

    /**
     * DbTablePopulator constructor.
     *
     * @param PdoFactory|null    $pdoFactory
     * @param TableFilterer|null $tableFilterer
     */
    public function __construct(PdoFactory $pdoFactory = null, TableFilterer $tableFilterer = null)
    {
        $this->pdoFactory = $pdoFactory ?: new PdoFactory();
        $this->tableFilterer = $tableFilterer ?: new TableFilterer();
    }

    /**
     * Populate the tables in a `ParsedSchema`
     *
     * @param ParsedSchema $parsedSchema
     *
     * @return ParsedSchema|null
     */
    public function populateTables(ParsedSchema $parsedSchema)
    {
        if (count($parsedSchema->getTables()) === 0) {
            $connection = $parsedSchema->getSchemaConfig()->getConnection();
            $pdo = $this->pdoFactory->getPdo($connection);

            $statement = $pdo->prepare(
                'SELECT table_name
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE table_schema = :schema
                AND table_type = "BASE TABLE"'
            );
            $statement->execute(['schema' => $parsedSchema->getSchemaName()]);
            $tables = $statement->fetchColumn(0);

            $tables = is_string($tables) ? [$tables] : $tables;

            if (count($parsedSchema->getSchemaConfig()->getExcludes()) > 0) {
                $tables = $this->tableFilterer->filter(
                    $tables,
                    $parsedSchema->getSchemaConfig()->getExcludes()
                );
            }

            $parsedSchema->setTables($tables);
        }

        if (count($parsedSchema->getTables()) === 0) {
            return null;
        }

        return $parsedSchema;
    }
}
