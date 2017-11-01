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

use Graze\Sprout\Chop\Mysql\MysqlTableChopper;
use Graze\Sprout\Config\ConnectionConfigInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;

class TableChopperFactory
{
    /** @var OutputInterface */
    private $output;

    /**
     * TableDumperFactory constructor.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function getChopper(ConnectionConfigInterface $connection): TableChopperInterface
    {
        $driver = $connection->getDriver();

        switch ($driver) {
            case 'mysql':
                $this->output->writeln(
                    "Using mysql table chopper for driver: {$driver}",
                    OutputInterface::VERBOSITY_DEBUG
                );
                return new MysqlTableChopper($connection, $this->output);
            default:
                throw new InvalidArgumentException("getChopper: no chopper found for driver: `{$driver}`");
        }
    }
}
