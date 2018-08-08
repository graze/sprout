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

namespace Graze\Sprout\Seed\Mysql;

use Graze\ParallelProcess\Pool;
use Graze\ParallelProcess\Table;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Seed\TableSeederInterface;
use InvalidArgumentException;
use Symfony\Component\Process\Process;

class MysqlTableSeeder implements TableSeederInterface
{
    /** @var ConnectionConfigInterface */
    private $connection;
    /** @var Pool */
    private $pool;

    /**
     * MysqlTableDumper constructor.
     *
     * @param Pool                     $pool
     * @param ConnectionConfigInterface $connection
     */
    public function __construct(Pool $pool, ConnectionConfigInterface $connection)
    {
        $this->pool = $pool;
        $this->connection = $connection;
    }

    /**
     * @param string $file
     * @param string $schema
     * @param string $table
     */
    public function seed(string $file, string $schema, string $table)
    {
        if (!file_exists($file)) {
            throw new InvalidArgumentException("seed: The file: {$file} does not exist");
        }

        $process = new Process('');
        $process->setCommandLine(
            sprintf(
                'mysql -h%1$s -u%2$s -p%3$s --default-character-set=utf8 %4$s < %5$s',
                escapeshellarg($this->connection->getHost()),
                escapeshellarg($this->connection->getUser()),
                escapeshellarg($this->connection->getPassword()),
                escapeshellarg($schema),
                escapeshellarg($file)
            )
        );

        $this->pool->add($process, ['action' => 'seed', 'schema' => $schema, 'table' => $table]);
    }
}
