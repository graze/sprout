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

namespace Graze\Sprout\File\Php;

use Graze\ParallelProcess\CallbackRun;
use Graze\ParallelProcess\PoolInterface;
use Graze\Sprout\Chop\TableChopperInterface;
use Graze\Sprout\Db\DbFactoryInterface;
use Graze\Sprout\Db\Doctrine\DoctrineFactory;
use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\Table;
use Graze\Sprout\File\Reader\ReaderInterface;
use Graze\Sprout\SeedDataInterface;
use InvalidArgumentException;
use League\Flysystem\AdapterInterface;

class FileTableChopper implements TableChopperInterface
{
    /** @var PoolInterface */
    private $pool;
    /** @var ReaderInterface */
    private $reader;
    /** @var AdapterInterface */
    private $filesystem;
    /** @var DbFactoryInterface */
    private $factory;

    /**
     * MysqlTableDumper constructor.
     *
     * @param ReaderInterface         $reader
     * @param PoolInterface           $pool
     * @param AdapterInterface        $filesystem
     * @param DbFactoryInterface|null $factory
     */
    public function __construct(
        ReaderInterface $reader,
        PoolInterface $pool,
        AdapterInterface $filesystem,
        DbFactoryInterface $factory = null
    ) {
        $this->reader = $reader;
        $this->pool = $pool;
        $this->filesystem = $filesystem;
        $this->factory = $factory ?: new DoctrineFactory();
    }

    /**
     * Take a file, and write the contents into the table within the specified schema
     *
     * @param Schema $schema
     * @param Table  ...$tables
     *
     * @return void
     */
    public function chop(Schema $schema, Table ...$tables)
    {
        foreach ($tables as $table) {
            if (!$this->filesystem->has($table->getPath())) {
                throw new InvalidArgumentException("chop: The file: `{$table->getPath()}` does not exist");
            }

            $this->pool->add(
                new CallbackRun(
                    function () use ($schema, $table) {
                        $db = $this->factory->getDb($schema->getSchemaConfig());
                        $seedData = $this->reader->parse($table);
                        if ($seedData->getSeedType() === SeedDataInterface::SEED_TYPE_TRUNCATE) {
                            $db->truncate($seedData->getTableName());
                        }
                    },
                    ['chop', 'schema' => $schema->getSchemaName(), 'table' => $table->getName()]
                )
            );
        }
    }
}
