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

use Graze\Sprout\Config\SchemaConfigInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class Chopper
{
    /** @var SchemaConfigInterface */
    private $schemaConfig;
    /** @var OutputInterface */
    private $output;
    /** @var TableChopperFactory */
    private $factory;

    /**
     * Chopper constructor.
     *
     * @param SchemaConfigInterface $schemaConfig
     * @param OutputInterface       $output
     * @param TableChopperFactory   $factory
     */
    public function __construct(
        SchemaConfigInterface $schemaConfig,
        OutputInterface $output,
        TableChopperFactory $factory = null
    ) {
        $this->schemaConfig = $schemaConfig;
        $this->output = $output;
        $this->factory = $factory ?: new TableChopperFactory($output);
    }

    /**
     * Chop a collection of files to tables
     *
     * @param string[] $tables
     */
    public function chop(array $tables = [])
    {
        $tables = array_unique($tables);

        if (count($tables) === 0) {
            $this->output->writeln('<warning>No tables specified, nothing to do</warning>');
            return;
        }

        $tableChopper = $this->factory->getChopper($this->schemaConfig->getConnection());
        $schema = $this->schemaConfig->getSchema();

        foreach ($tables as $table) {
            $tableChopper->chop($schema, $table);
        }
    }
}
