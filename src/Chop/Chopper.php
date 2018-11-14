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

namespace Graze\Sprout\Chop;

use Graze\Sprout\Config\SchemaConfigInterface;
use Graze\Sprout\Db\Schema;
use Graze\Sprout\TablePopulatorInterface;
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
     * @param SchemaConfigInterface   $schemaConfig
     * @param OutputInterface         $output
     * @param TableChopperFactory     $factory
     */
    public function __construct(
        SchemaConfigInterface $schemaConfig,
        OutputInterface $output,
        TableChopperFactory $factory
    ) {
        $this->schemaConfig = $schemaConfig;
        $this->output = $output;
        $this->factory = $factory;
    }

    /**
     * Chop a collection of files to tables
     *
     * @param Schema $schema
     */
    public function chop(Schema $schema)
    {
        if (count($schema->getTables()) === 0) {
            $this->output->writeln('<warning>No tables specified, nothing to do</warning>');
            return;
        }

        // map each table into a collection matching the chopper
        $tableChoppers = [];
        foreach ($schema->getTables() as $table) {
            $tableChopper = $this->factory->getChopper($schema, $table);
            $hash = spl_object_hash($tableChopper);
            if (!isset($tableChoppers[$hash])) {
                $tableChoppers[$hash] = [
                    'chopper' => $tableChopper,
                    'tables'  => [],
                ];
            }
            $tableChoppers[$hash]['tables'][] = $table;
        }

        foreach ($tableChoppers as $chopperInfo) {
            /** @var TableChopperInterface $chopper */
            $chopper = $chopperInfo['chopper'];
            $chopper->chop($schema, ...$chopperInfo['tables']);
        }
    }
}
