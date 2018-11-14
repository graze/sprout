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
use Graze\ParallelProcess\Run;
use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\Table;
use Graze\Sprout\Seed\TableSeederInterface;
use InvalidArgumentException;
use League\Flysystem\AdapterInterface;
use Symfony\Component\Process\Process;

class MysqlTableSeeder implements TableSeederInterface
{
    /** @var Pool */
    private $pool;
    /** @var AdapterInterface */
    private $filesystem;

    /**
     * MysqlTableDumper constructor.
     *
     * @param Pool             $pool
     * @param AdapterInterface $filesystem
     */
    public function __construct(Pool $pool, AdapterInterface $filesystem)
    {
        $this->pool = $pool;
        $this->filesystem = $filesystem;
    }

    /**
     * @param Schema $schema
     * @param Table  $table
     */
    public function seed(Schema $schema, Table $table)
    {
        if ($this->filesystem->has($table->getPath()) === false) {
            throw new InvalidArgumentException("seed: The file: {$table->getPath()} does not exist");
        }

        $connection = $schema->getSchemaConfig()->getConnection();

        $process = new Process('');
        $process->setCommandLine(
            sprintf(
                '(echo %6$s; cat %5$s; echo %7$s) | mysql -h%1$s -u%2$s -p%3$s --max_allowed_packet=512M --default-character-set=utf8 %4$s',
                escapeshellarg($connection->getHost()),
                escapeshellarg($connection->getUser()),
                escapeshellarg($connection->getPassword()),
                escapeshellarg($schema->getSchemaName()),
                escapeshellarg($table->getPath()),
                escapeshellarg('SET AUTOCOMMIT=0; SET FOREIGN_KEY_CHECKS=0;'),
                escapeshellarg('SET AUTOCOMMIT=1; SET FOREIGN_KEY_CHECKS=1;')
            )
        );

        $this->pool->add(new Run(
            $process,
            ['seed', 'schema' => $schema->getSchemaName(), 'table' => $table->getName()],
            filesize($table->getPath())
        ));
    }
}
