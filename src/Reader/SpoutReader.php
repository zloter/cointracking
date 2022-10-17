<?php

namespace Zloter\Cointracking\Reader;

use OpenSpout\Reader\Common\Creator\ReaderFactory;
use Zloter\Cointracking\Types\Column;

/**
 * This class can take csv, xlsx and odt files and read them line by line
 * thanks to it's possible to read large files
 */
class SpoutReader extends SpreadSheetReader
{
    /**
     * @param callable $f
     * @param string $filename
     * @return mixed|void
     * @throws \OpenSpout\Common\Exception\IOException
     * @throws \OpenSpout\Common\Exception\UnsupportedTypeException
     * @throws \OpenSpout\Reader\Exception\ReaderNotOpenedException
     */
    public function readRows(callable $processRow, callable $processBatch, string $filename): void
    {
        $reader = ReaderFactory::createFromFile($filename);
        $reader->open($filename);
        $transactions = [];
        $index = 0;
        $currentTimestamp = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $line = $row->toArray();
                if (0 === $index) {
                    $headers = $this->mapHeaders($line);
                } else {
                    if (strtotime($this->getCell($line, $headers, Column::Time)) !== $currentTimestamp) {
                        $processBatch($transactions);
                        $transactions = [];
                    }
                    $transactions = $processRow($line, $index, $transactions, $headers);
                    $currentTimestamp = strtotime($this->getCell($line, $headers, Column::Time));
                }
                $index++;
            }
            $processBatch($transactions);
        }
    }
}