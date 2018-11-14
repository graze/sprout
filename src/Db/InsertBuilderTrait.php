<?php

namespace Graze\Sprout\Db;

trait InsertBuilderTrait
{
    /**
     * @param array $row
     * @param array $bindValues
     *
     * @return string
     */
    protected function buildInsertRow(array $row, array &$bindValues): string
    {
        $values = [];
        foreach ($row as $value) {
            $values[] = '?';
            $bindValues[] = $value;
        }
        return '(' . implode(',', $values) . ')';
    }

    /**
     * Group the rows into rows with common fields
     *
     * @param array $data
     *
     * @return array grouped data into rows with the same fields
     */
    protected function collateFields(array $data): array
    {
        $hash = function (array $row) {
            $keys = array_keys($row);
            sort($keys);
            return implode(',', $keys);
        };
        $grouped = [];
        foreach ($data as $row) {
            $grouped[$hash($row)][] = $row;
        }

        return $grouped;
    }
}
