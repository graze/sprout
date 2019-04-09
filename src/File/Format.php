<?php

namespace Graze\Sprout\File;

class Format
{
    const TYPE_SQL  = 'sql';
    const TYPE_PHP  = 'php';
    const TYPE_CSV  = 'csv';
    const TYPE_YAML = 'yaml';
    const TYPE_JSON = 'json';

    const EXT_SQL  = 'sql';
    const EXT_PHP  = 'php';
    const EXT_CSV  = 'csv';
    const EXT_YAML = 'yaml';
    const EXT_YML  = 'yml';
    const EXT_JSON = 'json';

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
            case static::EXT_SQL:
            case static::EXT_PHP:
            case static::EXT_CSV:
            case static::EXT_JSON:
            case static::EXT_YAML:
                return $format;
            case static::EXT_YML:
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
            static::EXT_SQL,
            static::EXT_PHP,
            static::EXT_CSV,
            static::EXT_YAML,
            static::EXT_YML,
            static::EXT_JSON,
        ];
    }
}
