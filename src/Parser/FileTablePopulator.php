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

namespace Graze\Sprout\Parser;

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
     * @param ParsedSchema $parsedSchema
     *
     * @return ParsedSchema|null
     */
    public function populateTables(ParsedSchema $parsedSchema): ParsedSchema
    {
        if (count($parsedSchema->getTables()) === 0) {
            if ($parsedSchema->getPath() === '' || $this->filesystem->has($parsedSchema->getPath()) === false) {
                return null;
            }

            // find existing tables
            $files = $this->filesystem->listContents($parsedSchema->getPath());
            $files = array_values(array_filter(
                $files,
                function (array $file) {
                    // ignore empty file names (`.bla`) files
                    return (pathinfo($file['path'], PATHINFO_FILENAME) !== '');
                }
            ));

            // sort by file size, largest first
            usort(
                $files,
                function (array $a, array $b) {
                    return ($a['size'] == $b['size']) ? 0 : (($a['size'] > $b['size']) ? -1 : 1);
                }
            );

            // remove the file extensions to get the table names
            $parsedSchema->setTables(array_map(
                function (array $file) {
                    return pathinfo($file['path'], PATHINFO_FILENAME);
                },
                $files
            ));
        }

        if (count($parsedSchema->getSchemaConfig()->getExcludes()) > 0) {
            $parsedSchema->setTables(
                $this->tableFilterer->filter(
                    $parsedSchema->getTables(),
                    $parsedSchema->getSchemaConfig()->getExcludes()
                )
            );
        }

        if (count($parsedSchema->getTables()) === 0) {
            return null;
        }

        return $parsedSchema;
    }
}
