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

namespace Graze\Sprout\File\Populator;

use Graze\Sprout\Db\Parser\TableFilterer;
use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\Table;
use Graze\Sprout\TablePopulatorInterface;
use League\Flysystem\AdapterInterface;

class FileTablePopulator implements TablePopulatorInterface
{
    /** @var AdapterInterface */
    private $filesystem;
    /** @var TableFilterer */
    private $tableFilterer;

    /**
     * SchemaParser constructor.
     *
     * @param AdapterInterface   $filesystem
     * @param TableFilterer|null $tableFilterer
     */
    public function __construct(AdapterInterface $filesystem, TableFilterer $tableFilterer = null)
    {
        $this->filesystem = $filesystem;
        $this->tableFilterer = $tableFilterer ?: new TableFilterer();
    }

    /**
     * @param Schema $schema
     *
     * @return Schema|null
     */
    public function populateTables(Schema $schema)
    {
        if (count($schema->getTables()) === 0) {
            if ($schema->getPath() === '' || $this->filesystem->has($schema->getPath()) === false) {
                return null;
            }

            // find existing tables
            $files = $this->filesystem->listContents($schema->getPath());
            $files = array_values(array_filter(
                $files,
                function (array $file) {
                    // ignore empty file names (`.bla`) files
                    return (pathinfo($file['path'], PATHINFO_FILENAME) !== '');
                }
            ));

            // remove the file extensions to get the table names
            $tables = array_map(
                function (array $file) {
                    return new Table(pathinfo($file['path'], PATHINFO_FILENAME), $file['path']);
                },
                $files
            );

            if (count($schema->getSchemaConfig()->getExcludes()) > 0) {
                $tables = $this->tableFilterer->filter(
                    $tables,
                    $schema->getSchemaConfig()->getExcludes()
                );
            }

            $schema->setTables($tables);
        }

        if (count($schema->getTables()) === 0) {
            return null;
        }

        return $schema;
    }
}
