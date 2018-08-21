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
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Seed\TableSeederInterface;
use InvalidArgumentException;
use League\Flysystem\AdapterInterface;
use Symfony\Component\Process\Process;

class MysqlTableSeeder implements TableSeederInterface
{
    /** @var ConnectionConfigInterface */
    private $connection;
    /** @var Pool */
    private $pool;
    /** @var AdapterInterface */
    private $fileSystem;

    /**
     * MysqlTableDumper constructor.
     *
     * @param Pool                      $pool
     * @param ConnectionConfigInterface $connection
     * @param AdapterInterface          $fileSystem
     */
    public function __construct(Pool $pool, ConnectionConfigInterface $connection, AdapterInterface $fileSystem)
    {
        $this->pool = $pool;
        $this->connection = $connection;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @param string $file
     * @param string $schema
     * @param string $table
     */
    public function seed(string $file, string $schema, string $table)
    {
        if ($this->fileSystem->has($file) === false) {
            throw new InvalidArgumentException("seed: The file: {$file} does not exist");
        }

        $process = new Process('');
        $process->setCommandLine(
            sprintf(
                '(echo %6$s; cat %5$s; echo %7$s) | mysql -h%1$s -u%2$s -p%3$s --max_allowed_packet=512M --default-character-set=utf8 %4$s',
                escapeshellarg($this->connection->getHost()),
                escapeshellarg($this->connection->getUser()),
                escapeshellarg($this->connection->getPassword()),
                escapeshellarg($schema),
                escapeshellarg($file),
                escapeshellarg('SET AUTOCOMMIT = 0; SET FOREIGN_KEY_CHECKS=0;'),
                escapeshellarg('SET AUTOCOMMIT = 1; SET FOREIGN_KEY_CHECKS=1;')
            )
        );

        $this->pool->add($process, ['seed', 'schema' => $schema, 'table' => $table]);
    }
}
