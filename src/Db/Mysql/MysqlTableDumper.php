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
use Graze\Sprout\Dump\TableDumperInterface;
use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\Table;
use Symfony\Component\Process\Process;

class MysqlTableDumper implements TableDumperInterface
{
    /** @var Pool */
    private $pool;

    /**
     * MysqlTableDumper constructor.
     *
     * @param Pool $pool
     */
    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @param Schema $schema
     * @param Table  $table
     */
    public function dump(Schema $schema, Table $table)
    {
        // ignore the path in the $table object, as we want to define the extension
        $path = sprintf('%s/%s.sql', $schema->getPath(), $table->getName());
        $connection = $schema->getSchemaConfig()->getConnection();
        $process = new Process('');
        $process->setCommandLine(
            sprintf(
                'mysqldump -h%1$s -u%2$s -p%3$s --compress --compact --no-create-info' .
                ' --extended-insert --hex-blob --quick --complete-insert %4$s %5$s ' .
                '| process-mysqldump > %6$s',
                escapeshellarg($connection->getHost()),
                escapeshellarg($connection->getUser()),
                escapeshellarg($connection->getPassword()),
                escapeshellarg($schema->getSchemaName()),
                escapeshellarg($table->getName()),
                escapeshellarg($path)
            )
        );

        $this->pool->add($process, ['dump', 'schema' => $schema->getSchemaName(), 'table' => $table->getName()]);
    }
}
