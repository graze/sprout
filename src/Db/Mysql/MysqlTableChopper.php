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

namespace Graze\Sprout\Db\Mysql;

use Graze\ParallelProcess\Pool;
use Graze\ParallelProcess\PoolInterface;
use Graze\Sprout\Chop\TableChopperInterface;
use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\Table;
use Symfony\Component\Process\Process;

class MysqlTableChopper implements TableChopperInterface
{
    /** @var Pool */
    private $pool;

    /**
     * MysqlTableDumper constructor.
     *
     * @param PoolInterface $pool
     */
    public function __construct(PoolInterface $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @param Schema $schema
     * @param Table  ...$tables
     */
    public function chop(Schema $schema, Table ...$tables)
    {
        $query = sprintf(
            'SET FOREIGN_KEY_CHECKS=0; %s; SET FOREIGN_KEY_CHECKS=1;',
            implode(
                '; ',
                array_map(
                    function (Table $table) {
                        return "TRUNCATE `{$table->getName()}`";
                    },
                    $tables
                )
            )
        );

        $connection = $schema->getSchemaConfig()->getConnection();

        $process = new Process('');
        $process->setCommandLine(
            sprintf(
                'mysql -h%1$s -u%2$s -p%3$s --default-character-set=utf8 --execute=%5$s %4$s',
                escapeshellarg($connection->getHost()),
                escapeshellarg($connection->getUser()),
                escapeshellarg($connection->getPassword()),
                escapeshellarg($schema->getSchemaName()),
                escapeshellarg($query)
            )
        );

        if (count($tables) < 3) {
            $displayTables = implode(
                ',',
                array_map(
                    function (Table $table) {
                        return $table->getName();
                    },
                    $tables
                )
            );
        } else {
            $displayTables = count($tables);
        }
        $this->pool->add($process, ['chop', 'schema' => $schema->getSchemaName(), 'tables' => $displayTables]);
    }
}
