<?php
/**
 * This file is part of graze/sprout.
 *
 * Copyright (c) 2017 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/sprout/blob/master/LICENSE.md
 * @link    https://github.com/graze/sprout
 */

namespace Graze\Sprout\Dump\Mysql;

use Graze\ParallelProcess\Table;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Dump\TableDumperInterface;
use Symfony\Component\Process\Process;

class MysqlTableDumper implements TableDumperInterface
{
    /** @var ConnectionConfigInterface */
    private $connection;
    /** @var Table */
    private $pool;

    /**
     * MysqlTableDumper constructor.
     *
     * @param Table                     $pool
     * @param ConnectionConfigInterface $connection
     */
    public function __construct(Table $pool, ConnectionConfigInterface $connection)
    {
        $this->connection = $connection;
        $this->pool = $pool;
    }

    /**
     * @param string $schema
     * @param string $table
     * @param string $file
     */
    public function dump(string $schema, string $table, string $file)
    {
        $process = new Process('');
        $process->setCommandLine(
            sprintf(
                'mysqldump -h%1$s -u%2$s -p%3$s --compress --compact --no-create-info' .
                ' --extended-insert --hex-dump --quick %4$s %5$s' .
                '| sed \'s$VALUES ($VALUES\n($g\' | sed \'s$),($),\n($g\' > %6$s',
                escapeshellarg($this->connection->getHost()),
                escapeshellarg($this->connection->getUser()),
                escapeshellarg($this->connection->getPassword()),
                escapeshellarg($schema),
                escapeshellarg($table),
                escapeshellarg($file)
            )
        );

        $this->pool->add($process, ['schema' => $schema, 'table' => $table]);
    }
}
