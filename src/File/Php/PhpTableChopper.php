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

namespace Graze\Sprout\Db\SeedData;

use Graze\ParallelProcess\CallbackRun;
use Graze\ParallelProcess\PoolInterface;
use Graze\Sprout\Chop\TableChopperInterface;
use Graze\Sprout\ChopperInterface;
use Graze\Sprout\Db\DbFactoryInterface;
use Graze\Sprout\Db\Doctrine\DoctrineFactory;
use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\Table;
use Graze\Sprout\SeedDataInterface;
use InvalidArgumentException;
use League\Flysystem\AdapterInterface;

class PhpTableChopper implements TableChopperInterface
{
    /** @var PoolInterface */
    private $pool;
    /** @var AdapterInterface */
    private $filesystem;
    /** @var DbFactoryInterface */
    private $dbFactory;

    /**
     * MysqlTableDumper constructor.
     *
     * @param PoolInterface           $pool
     * @param AdapterInterface        $filesystem
     * @param DbFactoryInterface|null $dbFactory
     */
    public function __construct(
        PoolInterface $pool,
        AdapterInterface $filesystem,
        DbFactoryInterface $dbFactory = null
    ) {
        $this->pool = $pool;
        $this->filesystem = $filesystem;
        $this->dbFactory = $dbFactory ?: new DoctrineFactory();
    }

    /**
     * @param Schema $schema
     * @param Table  ...$tables
     */
    public function chop(Schema $schema, Table ...$tables)
    {
        foreach ($tables as $table) {
            $this->chopFile($schema, $table);
        }
    }

    /**
     * @param Schema $schema
     * @param Table  $table
     */
    private function chopFile(Schema $schema, Table $table)
    {
        if (!$this->filesystem->has($table->getPath())) {
            throw new InvalidArgumentException("chop: The file: `{$table->getPath()}`` does not exist");
        }

        $seeder = include $table->getPath();

        if (is_null($seeder)) {
            throw new \RuntimeException("chop: The supplied file: `{$table->getPath()}` does not return an object");
        }

        if ($seeder instanceof ChopperInterface) {
            $this->pool->add(
                new CallbackRun(function () use ($seeder, $schema) {
                    $db = $this->dbFactory->getDb($schema->getSchemaConfig());
                    $seeder->chop($db);
                }),
                ['seed', 'schema' => $schema->getSchemaName(), 'table' => $table->getName()]
            );
        } elseif ($seeder instanceof SeedDataInterface) {
            if ($seeder->getSeedType() === SeedDataInterface::SEED_TYPE_TRUNCATE) {
                $this->pool->add(
                    new CallbackRun(function () use ($seeder, $schema) {
                        $db = $this->dbFactory->getDb($schema->getSchemaConfig());
                        $db->truncate($seeder->getTableName());
                    }),
                    ['seed', 'schema' => $schema->getSchemaName(), 'table' => $seeder->getTableName()]
                );
            }
        } else {
            throw new \RuntimeException("chop: The supplied file: `{$table->getPath()}` does not return an object that implements `ChopperInterface` or `SeedDataInterface`");
        }
    }
}
