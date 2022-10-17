<?php

namespace Zloter\Cointracking\Services;

use Zloter\Cointracking\Types\Column;

abstract class SpreadSheetReader
{
    /**
     * @param callable $f
     * @param string $filename
     * @return mixed
     */
    public abstract function readRows(callable $f, string $filename);


    /**
     * @param array $line
     * @param array $headers
     * @param Column $column
     * @return mixed
     */
    public function getCell(array $line, array $headers, Column $column): mixed
    {
        return $line[$headers[$column->value]];
    }

    /**
     * @param array $headers
     * @return array
     */
    public function mapHeaders(array $headers): array
    {
        return [
            Column::Time->value => array_search("UTC_Time", $headers),
            Column::Type->value => array_search("Operation", $headers),
            Column::Operation->value => array_search("Operation", $headers),
            Column::Currency->value => array_search("Coin", $headers),
            Column::Amount->value => array_search("Change", $headers)
        ];
    }
}