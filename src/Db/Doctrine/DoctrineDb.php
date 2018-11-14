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

namespace Graze\Sprout\Db\Doctrine;

use Doctrine\DBAL\Connection;
use Graze\Sprout\Db\DbInterface;
use Graze\Sprout\Db\InsertBuilderTrait;
use Throwable;
use Traversable;

class DoctrineDb implements DbInterface
{
    use InsertBuilderTrait;

    const CHUNK_SIZE = 1000;

    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $table
     * @param array  $data
     * @param string $duplicate
     *
     * @return int The number of inserted rows
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Throwable
     */
    public function insert(string $table, array $data, string $duplicate = self::INSERT_DUPLICATE_FAIL): int
    {
        if (count($data) == 0) {
            return 0;
        }

        switch ($duplicate) {
            case static::INSERT_DUPLICATE_IGNORE:
                $command = 'INSERT IGNORE';
                break;
            case static::INSERT_DUPLICATE_UPDATE:
                $command = 'REPLACE';
                break;
            default:
                $command = 'INSERT';
                break;
        }

        $groups = $this->collateFields($data);

        $inserted = 0;

        $this->connection->beginTransaction();
        try {
            $this->connection->executeUpdate("SET FOREIGN_KEY_CHECKS=0;");
            foreach ($groups as $group) {
                $fields = array_keys(reset($group));
                $insertRows = [];
                $bindValues = [];
                $numRows = count($group);
                $count = 0;

                foreach ($groups as $row) {
                    $count++;

                    $insertRows[] = $this->buildInsertRow($row, $bindValues);

                    if ($count % static::CHUNK_SIZE === 0 || $count == $numRows) {
                        $inserted += $this->connection->executeUpdate(
                            sprintf(
                                "%s INTO %s\n(%s)\nVALUES\n%s;",
                                $command,
                                $this->connection->quoteIdentifier($table),
                                implode(',', array_map([$this->connection, 'quoteIdentifier'], $fields)),
                                implode(',', $insertRows)
                            ),
                            $bindValues
                        );
                        $insertRows = [];
                        $bindValues = [];
                    }
                }
            }
            $this->connection->executeUpdate("SET FOREIGN_KEY_CHECKS=1;");
            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }

        return $inserted;
    }

    /**
     * @param string $table
     * @param array  $where
     *
     * @return int The number of rows deleted
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function delete(string $table, array $where): int
    {
        return $this->connection->delete($table, $where);
    }

    /**
     * Truncate a table
     *
     * @param string $table
     *
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function truncate(string $table)
    {
        $this->connection->executeUpdate("TRUNCATE {$this->connection->quoteIdentifier($table)}");
    }

    /**
     * @param string $table
     *
     * @return array
     */
    public function getFields(string $table): array
    {
        $fields = $this->connection->fetchAll("DESCRIBE {$this->connection->quoteIdentifier($table)}");
        return array_map(
            function (array $row) {
                return reset($row);
            },
            $fields
        );
    }

    /**
     * @param string $table
     *
     * @return array|Traversable
     */
    public function fetchAll(string $table)
    {
        return $this->connection->fetchAll("SELECT * FROM {$this->connection->quoteIdentifier($table)}");
    }
}
