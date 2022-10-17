<?php

namespace Zloter\Cointracking\Reader;


class ReaderFactory
{
    public static function createReader(string $extension): SpreadSheetReader
    {
        return match ($extension) {
            'xls' => new XlsReader(),
            default => new SpoutReader()
        };
    }
}