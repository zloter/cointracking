<?php

namespace Zloter\Cointracking\Services;

use OpenSpout\Reader\Common\Creator\ReaderFactory;
use Zloter\Cointracking\Types\Column;

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
    public function readRows(callable $f, string $filename)
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
                    $transactions = $f($line, $index, $transactions, $currentTimestamp, $headers);
                    $currentTimestamp = strtotime($this->getCell($line, $headers, Column::Time));
                }
                $index++;
            }
        }

        $reader->close();
    }
}