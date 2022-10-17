<?php

namespace Zloter\Cointracking\Reader;

use Zloter\Cointracking\Types\Column;

/**
 * It's library for reading xls as spout does not have functionality for it
 * As it loads whole file to memory big files aren't r
 * ecommended
 */
class XlsReader extends SpreadSheetReader
{
    /**
     * @param callable $f
     * @param string $filename
     * @return mixed|void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function readRows(callable $processRow, callable $processBatch, string $filename): void
    {
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filename);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly(true);
        $spreadsheet = $reader->load($filename);
        $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());
        $data = $sheet->toArray();

        $transactions = [];
        $index = 0;
        $currentTimestamp = 0;

        foreach ($data as $line) {
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