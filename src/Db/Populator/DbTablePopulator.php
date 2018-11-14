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

namespace Graze\Sprout\Db\Populator;

use Graze\Sprout\Db\Parser\TableFilterer;
use Graze\Sprout\Db\Pdo\PdoFactory;
use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\Table;
use Graze\Sprout\TablePopulatorInterface;
use PDO;

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
     * Populate the tables in a `Schema`
     *
     * @param Schema $schema
     *
     * @return Schema|null
     */
    public function populateTables(Schema $schema)
    {
        if (count($schema->getTables()) === 0) {
            $connection = $schema->getSchemaConfig()->getConnection();
            $pdo = $this->pdoFactory->getPdo($connection);

            $statement = $pdo->prepare(
                'SELECT table_name
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE table_schema = :schema
                AND table_type = "BASE TABLE"'
            );
            $statement->execute(['schema' => $schema->getSchemaName()]);
            $tables = $statement->fetchAll(PDO::FETCH_COLUMN);

            if (count($schema->getSchemaConfig()->getExcludes()) > 0) {
                $tables = $this->tableFilterer->filter(
                    $tables,
                    $schema->getSchemaConfig()->getExcludes()
                );
            }

            $tables = array_map(
                function (string $table) {
                    return new Table($table);
                },
                $tables
            );

            $schema->setTables($tables);
        }

        if (count($schema->getTables()) === 0) {
            return null;
        }

        return $schema;
    }
}
