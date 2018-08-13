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

namespace Graze\Sprout\Parser;

use League\Flysystem\AdapterInterface;
use SplFileInfo;

class TablePopulator
{
    /** @var AdapterInterface */
    private $filesystem;

    /**
     * SchemaParser constructor.
     *
     * @param AdapterInterface $filesystem
     */
    public function __construct(AdapterInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param ParsedSchema $parsedSchema
     *
     * @return ParsedSchema|null
     */
    public function populateTables(ParsedSchema $parsedSchema)
    {
        if (count($parsedSchema->getTables()) === 0) {
            if ($this->filesystem->has($parsedSchema->getPath()) === false) {
                return null;
            }

            // find existing tables
            $files = $this->filesystem->listContents($parsedSchema->getPath());
            $files = array_values(array_filter(
                $files,
                function (SplFileInfo $file) {
                    // ignore empty file names (`.bla`) files
                    return ($file->getBasename('.' . $file->getExtension()) !== '');
                }
            ));

            // sort by file size, largest first
            usort(
                $files,
                function (SplFileInfo $a, SplFileInfo $b) {
                    $left = $a->getSize();
                    $right = $b->getSize();
                    return ($left == $right) ? 0 : (($left > $right) ? -1 : 1);
                }
            );

            // remove the file extensions to get the table names
            $parsedSchema->setTables(array_map(
                function (SplFileInfo $file) {
                    return $file->getBasename('.' . $file->getExtension());
                },
                $files
            ));
        }

        if (count($parsedSchema->getTables()) === 0) {
            return null;
        }

        return $parsedSchema;
    }
}
