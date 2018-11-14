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
use Graze\Sprout\File\Reader\ReaderInterface;
use Graze\Sprout\Seed\TableSeederInterface;
use Graze\Sprout\SeedDataInterface;
use InvalidArgumentException;
use League\Flysystem\AdapterInterface;

class FileTableSeeder implements TableSeederInterface
{
    /** @var Pool */
    private $pool;
    /** @var ReaderInterface */
    private $reader;
    /** @var AdapterInterface */
    private $filesystem;
    /** @var DbInterface[] */
    private $db = [];
    /** @var DbFactoryInterface */
    private $factory;

    /**
     * MysqlTableDumper constructor.
     *
     * @param ReaderInterface         $reader
     * @param Pool                    $pool
     * @param AdapterInterface        $filesystem
     * @param DbFactoryInterface|null $factory
     */
    public function __construct(
        ReaderInterface $reader,
        Pool $pool,
        AdapterInterface $filesystem,
        DbFactoryInterface $factory = null
    ) {
        $this->reader = $reader;
        $this->pool = $pool;
        $this->filesystem = $filesystem;
        $this->factory = $factory ?: new DoctrineFactory();
    }

    /**
     * @param string $seedDataType SeedDataInterface::SEED_TYPE_*
     *
     * @return string DbInterface::INSERT_DUPLICATE_*
     */
    private function toDbInsertType(string $seedDataType)
    {
        switch ($seedDataType) {
            case SeedDataInterface::SEED_TYPE_TRUNCATE:
                return DbInterface::INSERT_DUPLICATE_FAIL;
            case SeedDataInterface::SEED_TYPE_IGNORE:
                return DbInterface::INSERT_DUPLICATE_IGNORE;
            case SeedDataInterface::SEED_TYPE_UPDATE:
                return DbInterface::INSERT_DUPLICATE_UPDATE;
            default:
                throw new \InvalidArgumentException("unknown seed data type: {$seedDataType}");
        }
    }

    /**
     * Take a file, and write the contents into the table within the specified schema
     *
     * @param Schema $schema
     * @param Table  $table
     *
     * @return void
     */
    public function seed(Schema $schema, Table $table)
    {
        if (!$this->filesystem->has($table->getPath())) {
            throw new InvalidArgumentException("seed: The file: `{$table->getPath()}` does not exist");
        }

        $this->pool->add(
            new CallbackRun(
                function () use ($table, $schema) {
                    $db = $this->factory->getDb($schema->getSchemaConfig());
                    $seedData = $this->reader->parse($table);
                    $db->insert(
                        $seedData->getTableName(),
                        $seedData->getData(),
                        $this->toDbInsertType($seedData->getSeedType())
                    );
                },
                ['seed', 'schema' => $schema->getSchemaName(), 'table' => $table->getName()],
                filesize($table->getPath())
            )
        );
    }
}
