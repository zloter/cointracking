<?php

namespace Zloter\Cointracking\Commands;


use Zloter\Cointracking\Services\FileManager;
use Zloter\Cointracking\Services\JsonFormatter;
use Zloter\Cointracking\Services\SpreadSheetReader;
use Zloter\Cointracking\Services\TransactionService;
use Zloter\Cointracking\Types\Column;
use Zloter\Cointracking\Types\Transaction;

class ProcessTransactionSheetToJson
{
    /**
     * @param FileManager $fileManager
     * @param TransactionService $transactionService
     * @param JsonFormatter $jsonFormatter
     * @param SpreadSheetReader $spreadSheetReader
     */
    public function __construct(
        private readonly FileManager $fileManager,
        private readonly TransactionService $transactionService,
        private readonly JsonFormatter $jsonFormatter,
        private readonly SpreadSheetReader $spreadSheetReader
    ) {}

    /**
     * @return void
     * @throws \OpenSpout\Common\Exception\IOException
     * @throws \OpenSpout\Common\Exception\UnsupportedTypeException
     * @throws \OpenSpout\Reader\Exception\ReaderNotOpenedException
     * @throws \Exception
     */
    public function __invoke(): void
    {
        $tmp = $this->fileManager->newTmp();
        $this->fileManager->save($tmp, $this->jsonFormatter->openArray());

        $this->spreadSheetReader->readRows(function (
            array $line,
            int $index,
            array $transactions,
            int $currentTimestamp,
            array $headers = []
        ) use ($tmp) {
            if ($currentTimestamp !== strtotime($this->spreadSheetReader->getCell($line, $headers, Column::Time))) {
                $transactions = $this->transactionService->sortAndMerge($transactions);
                $this->fileManager->save($tmp, $this->jsonFormatter->transactionsToJson($transactions));
                $transactions = [];
            }
            $income = 0 < $this->spreadSheetReader->getCell($line, $headers, Column::Amount);
            $transactions[$index] = new Transaction(
                strtotime($this->spreadSheetReader->getCell($line, $headers, Column::Time)),
                $this->transactionService->mapType($this->spreadSheetReader->getCell($line, $headers, Column::Type)),
                $income ? $this->spreadSheetReader->getCell($line, $headers, Column::Currency) : null,
                $income ? $this->spreadSheetReader->getCell($line, $headers, Column::Amount) : null,
                !$income ? $this->spreadSheetReader->getCell($line, $headers, Column::Currency) : null,
                !$income ? (-$this->spreadSheetReader->getCell($line, $headers, Column::Amount)) : null
            );
            return $transactions;
        }, $this->fileManager->getValidFileName());

        $this->fileManager->save($tmp, $this->jsonFormatter->closeArray());
        $this->fileManager->printToStdOutput($tmp);
        $this->fileManager->close($tmp);
    }
}