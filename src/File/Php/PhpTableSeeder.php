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
use Graze\Sprout\Seed\TableSeederInterface;
use Graze\Sprout\SeedDataInterface;
use Graze\Sprout\SeederInterface;
use InvalidArgumentException;
use League\Flysystem\AdapterInterface;

class PhpTableSeeder implements TableSeederInterface
{
    /** @var Pool */
    private $pool;
    /** @var AdapterInterface */
    private $filesystem;
    /** @var DbFactoryInterface */
    private $factory;

    /**
     * MysqlTableDumper constructor.
     *
     * @param Pool                    $pool
     * @param AdapterInterface        $filesystem
     * @param DbFactoryInterface|null $factory
     */
    public function __construct(Pool $pool, AdapterInterface $filesystem, DbFactoryInterface $factory = null)
    {
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

        $seeder = include $table->getPath();

        if (is_null($seeder)) {
            throw new \RuntimeException("seed: The file: `{$table->getPath()}` does not return an object");
        }

        if ($seeder instanceof SeederInterface) {
            $this->pool->add(
                new CallbackRun(function () use ($seeder, $schema) {
                    $db = $this->factory->getDb($schema->getSchemaConfig());
                    $seeder->seed($db);
                }),
                ['seed', 'schema' => $schema->getSchemaName(), 'table' => $table->getName()]
            );
        } elseif ($seeder instanceof SeedDataInterface) {
            $this->pool->add(
                new CallbackRun(function () use ($seeder, $schema) {
                    $db = $this->factory->getDb($schema->getSchemaConfig());
                    $db->insert(
                        $seeder->getTableName(),
                        $seeder->getData(),
                        $this->toDbInsertType($seeder->getSeedType())
                    );
                }),
                ['seed', 'schema' => $schema->getSchemaName(), 'table' => $seeder->getTableName()]
            );
        } else {
            throw new \RuntimeException("seed: The file: `{$table->getPath()}` does not return an object that implements `SeederInterface` or `SeedDataInterface`");
        }
    }
}
