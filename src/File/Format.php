<?php

namespace Graze\Sprout\File;

class Format
{
    const TYPE_SQL  = 'sql';
    const TYPE_PHP  = 'php';
    const TYPE_CSV  = 'csv';
    const TYPE_YAML = 'yaml';
    const TYPE_JSON = 'json';

    /**
     * @param string $format
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public static function parseFormat(string $format): string
    {
        switch (strtolower($format)) {
            case static::TYPE_SQL:
            case static::TYPE_PHP:
            case static::TYPE_CSV:
            case static::TYPE_JSON:
                return $format;
            case static::TYPE_YAML:
            case 'yml':
                return static::TYPE_YAML;
            default:
                throw new \InvalidArgumentException(
                    "parse format: Supplied format: {$format} could not be parsed, expected one of: "
                    . implode(', ', static::getFormats())
                );
        }
    }

    /**
     * Get a list of all formats
     *
     * @return string[]
     */
    public static function getFormats(): array
    {
        return [
            static::TYPE_SQL,
            static::TYPE_PHP,
            static::TYPE_CSV,
            static::TYPE_YAML,
            'yml',
            static::TYPE_JSON,
        ];
    }
}
