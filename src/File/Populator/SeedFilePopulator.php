<?php

namespace Graze\Sprout\File\Populator;

use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\Table;
use Graze\Sprout\TablePopulatorInterface;
use League\Flysystem\AdapterInterface;

/**
 * SeedFile Populator takes a set of tables and attempts to match them up with existing files. If multiple files with
 * the same name (but different extension) exist, then the table is duplicated and all the entries are included
 *
 * @package Graze\Sprout\File\Populator
 */
class SeedFilePopulator implements TablePopulatorInterface
{
    /** @var AdapterInterface */
    private $filesystem;

    /**
     * SeedFilePopulator constructor.
     *
     * @param AdapterInterface $filesystem
     */
    public function __construct(AdapterInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Populate the tables in a `Schema`. If there are no actual tables, return `null`
     *
     * @param Schema $schema
     *
     * @return Schema|null
     */
    public function populateTables(Schema $schema)
    {
        if ($schema->getPath() === '' || $this->filesystem->has($schema->getPath()) === false) {
            return $schema;
        }
        if (count($schema->getTables()) > 0) {
            $files = $this->filesystem->listContents($schema->getPath());

            $outputTables = [];

            foreach ($schema->getTables() as $table) {
                if ($table->getPath() !== '') {
                    $outputTables[] = $table;
                    continue;
                }

                $tableFiles = array_filter(
                    $files,
                    function (array $file) use ($table) {
                        return (pathinfo($file['path'], PATHINFO_FILENAME) == $table->getName());
                    }
                );

                if (count($tableFiles) === 0) {
                    $outputTables[] = $table;
                }
                foreach ($tableFiles as $file) {
                    $outputTables[] = new Table($table->getName(), $file['path']);
                }
            }
            $schema->setTables($outputTables);
        }

        return $schema;
    }
}
