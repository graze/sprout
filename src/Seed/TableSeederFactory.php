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

namespace Graze\Sprout\Seed;

use Graze\ParallelProcess\Pool;
use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Db\Mysql\MysqlTableSeeder;
use InvalidArgumentException;
use League\Flysystem\AdapterInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class TableSeederFactory implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var Pool */
    private $processPool;
    /** @var AdapterInterface */
    private $filesystem;

    /**
     * TableDumperFactory constructor.
     *
     * @param Pool             $processPool
     * @param AdapterInterface $filesystem
     */
    public function __construct(Pool $processPool, AdapterInterface $filesystem)
    {
        $this->processPool = $processPool;
        $this->filesystem = $filesystem;
    }

    /**
     * @param ConnectionConfigInterface $connection
     *
     * @return TableSeederInterface
     */
    public function getSeeder(ConnectionConfigInterface $connection): TableSeederInterface
    {
        $driver = $connection->getDriver();

        switch ($driver) {
            case 'mysql':
                if ($this->logger) {
                    $this->logger->debug(
                        "getSeeder: using mysql seeder for driver: {$driver}",
                        ['driver' => $driver]
                    );
                }
                return new MysqlTableSeeder($this->processPool, $connection, $this->filesystem);
            default:
                throw new InvalidArgumentException("getSeeder: no seeder found for driver: `{$driver}`");
        }
    }
}
