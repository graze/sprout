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

namespace Graze\Sprout\Chop;

use Graze\ParallelProcess\Pool;
use Graze\Sprout\Chop\Mysql\MysqlTableChopper;
use Graze\Sprout\Config\ConnectionConfigInterface;
use InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class TableChopperFactory implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var Pool */
    private $processPool;

    /**
     * TableDumperFactory constructor.
     *
     * @param Pool $processPool
     *
     * @internal param OutputInterface $output
     */
    public function __construct(Pool $processPool)
    {
        $this->processPool = $processPool;
    }

    /**
     * @param ConnectionConfigInterface $connection
     *
     * @return TableChopperInterface
     */
    public function getChopper(ConnectionConfigInterface $connection): TableChopperInterface
    {
        $driver = $connection->getDriver();

        switch ($driver) {
            case 'mysql':
                if ($this->logger) {
                    $this->logger->debug(
                        "getChopper: using mysql chopper for driver: {$driver}",
                        ['driver' => $driver]
                    );
                }
                return new MysqlTableChopper($this->processPool, $connection);
            default:
                throw new InvalidArgumentException("getChopper: no chopper found for driver: `{$driver}`");
        }
    }
}
