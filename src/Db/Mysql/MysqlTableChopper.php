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
use Graze\Sprout\Chop\TableChopperInterface;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Symfony\Component\Process\Process;

class MysqlTableChopper implements TableChopperInterface
{
    /** @var ConnectionConfigInterface */
    private $connection;
    /** @var Pool */
    private $pool;

    /**
     * MysqlTableDumper constructor.
     *
     * @param Pool                      $pool
     * @param ConnectionConfigInterface $connection
     */
    public function __construct(Pool $pool, ConnectionConfigInterface $connection)
    {
        $this->connection = $connection;
        $this->pool = $pool;
    }

    /**
     * @param string $schema
     * @param string ...$tables
     */
    public function chop(string $schema, string ...$tables)
    {
        $query = sprintf(
            'SET FOREIGN_KEY_CHECKS=0; %s; SET FOREIGN_KEY_CHECKS=1;',
            implode(
                '; ',
                array_map(
                    function (string $table) {
                        return "TRUNCATE `{$table}`";
                    },
                    $tables
                )
            )
        );
        $process = new Process('');
        $process->setCommandLine(
            sprintf(
                'mysql -h%1$s -u%2$s -p%3$s --default-character-set=utf8 --execute=%5$s %4$s',
                escapeshellarg($this->connection->getHost()),
                escapeshellarg($this->connection->getUser()),
                escapeshellarg($this->connection->getPassword()),
                escapeshellarg($schema),
                escapeshellarg($query)
            )
        );

        $displayTables = (count($tables) < 3 ? implode(', ', $tables) : count($tables));
        $this->pool->add($process, ['chop', 'schema' => $schema, 'tables' => $displayTables]);
    }
}
