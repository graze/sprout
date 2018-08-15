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

namespace Graze\Sprout\Dump;

use Graze\Sprout\Config\SchemaConfigInterface;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class Dumper
{
    /** @var SchemaConfigInterface */
    private $schemaConfig;
    /** @var OutputInterface */
    private $output;
    /** @var TableDumperFactory */
    private $factory;
    /** @var AdapterInterface */
    private $filesystem;

    /**
     * Dumper constructor.
     *
     * @param SchemaConfigInterface $schemaConfig
     * @param OutputInterface       $output
     * @param TableDumperFactory    $factory
     * @param AdapterInterface      $filesystem
     */
    public function __construct(
        SchemaConfigInterface $schemaConfig,
        OutputInterface $output,
        TableDumperFactory $factory,
        AdapterInterface $filesystem
    ) {
        $this->schemaConfig = $schemaConfig;
        $this->output = $output;
        $this->factory = $factory;
        $this->filesystem = $filesystem;
    }

    /**
     * Dump a collection of tables to disk
     *
     * @param string   $path
     * @param string[] $tables
     */
    public function dump(string $path, array $tables = [])
    {
        $tables = array_unique($tables);

        if (count($tables) === 0) {
            $this->output->writeln('<warning>No tables specified, nothing to do</warning>');
            return;
        }

        if ($this->filesystem->createDir($path, new Config()) === false) {
            throw new RuntimeException('dump: failed to create directory: ' . $path);
        }

        $tableDumper = $this->factory->getDumper($this->schemaConfig->getConnection());
        $schema = $this->schemaConfig->getSchema();

        foreach ($tables as $table) {
            $file = sprintf('%s/%s.sql', $path, $table);
            $tableDumper->dump($schema, $table, $file);
        }
    }
}
