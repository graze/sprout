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
use Graze\ParallelProcess\Pool;
use Graze\Sprout\Db\DbFactoryInterface;
use Graze\Sprout\Db\DbInterface;
use Graze\Sprout\Db\Doctrine\DoctrineFactory;
use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\Table;
use Graze\Sprout\Dump\TableDumperInterface;
use Graze\Sprout\File\Reader\ReaderInterface;
use Graze\Sprout\File\Writer\WriterInterface;
use Graze\Sprout\SeedData;
use Graze\Sprout\SeedDataInterface;
use League\Flysystem\AdapterInterface;

class FileTableDumper implements TableDumperInterface
{
    /** @var Pool */
    private $pool;
    /** @var AdapterInterface */
    private $filesystem;
    /** @var DbInterface[] */
    private $db = [];
    /** @var DbFactoryInterface */
    private $factory;
    /** @var WriterInterface */
    private $writer;
    /** @var ReaderInterface */
    private $reader;
    /** @var string */
    private $seedType;
    /** @var bool */
    private $override;

    /**
     * MysqlTableDumper constructor.
     *
     * @param WriterInterface         $writer
     * @param ReaderInterface         $reader
     * @param Pool                    $pool
     * @param AdapterInterface        $filesystem
     * @param string                  $seedType
     * @param bool                    $override
     * @param DbFactoryInterface|null $factory
     */
    public function __construct(
        WriterInterface $writer,
        ReaderInterface $reader,
        Pool $pool,
        AdapterInterface $filesystem,
        string $seedType = SeedDataInterface::SEED_TYPE_TRUNCATE,
        bool $override = false,
        DbFactoryInterface $factory = null
    ) {
        $this->pool = $pool;
        $this->filesystem = $filesystem;
        $this->factory = $factory ?: new DoctrineFactory();
        $this->writer = $writer;
        $this->reader = $reader;
        $this->seedType = $seedType;
        $this->override = $override;
    }

    /**
     * Write all the contents of a table into a file
     *
     * @param Schema $schema
     * @param Table  $table
     *
     * @return void
     */
    public function dump(Schema $schema, Table $table)
    {
        $this->pool->add(
            new CallbackRun(
                function () use ($schema, $table) {
                    $db = $this->factory->getDb($schema->getSchemaConfig());
                    $rows = $db->fetch($table->getName());

                    $seedType = $this->seedType;
                    if (!$this->override && $this->filesystem->has($table->getPath())) {
                        $seedType = $this->reader->parse($table)->getSeedType();
                    }

                    $this->writer->write(
                        new SeedData($table->getName(), iterator_to_array($rows), $seedType),
                        $schema,
                        $table
                    );
                },
                ['dump', 'schema' => $schema->getSchemaName(), 'table' => $table->getName()]
            )
        );
    }
}
