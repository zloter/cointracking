<?php

namespace Zloter\Cointracking\Commands;


use Zloter\Cointracking\Services\ApiCurrencyValue;
use Zloter\Cointracking\Services\FileManager;
use Zloter\Cointracking\Services\JsonFormatter;
use Zloter\Cointracking\Reader\SpreadSheetReader;
use Zloter\Cointracking\Services\TransactionService;
use Zloter\Cointracking\Types\Column;
use Zloter\Cointracking\Types\Payment;
use Zloter\Cointracking\Types\Transaction;

class ProcessTransactionSheetToJson
{
    /**
     * @param FileManager $fileManager
     * @param TransactionService $transactionService
     * @param JsonFormatter $jsonFormatter
     * @param SpreadSheetReader $spreadSheetReader
     * @param ApiCurrencyValue $apiCurrencyValue
     */
    public function __construct(
        private readonly FileManager $fileManager,
        private readonly TransactionService $transactionService,
        private readonly JsonFormatter $jsonFormatter,
        private readonly SpreadSheetReader $spreadSheetReader,
        private readonly ApiCurrencyValue $apiCurrencyValue
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

        $this->spreadSheetReader->readRows(function (array $line, int $index, array $transactions, array $headers = []) {
            $currency = $this->spreadSheetReader->getCell($line, $headers, Column::Currency);
            $amount =  $this->spreadSheetReader->getCell($line, $headers, Column::Amount);
            $income = 0 < $amount;
            $time = strtotime($this->spreadSheetReader->getCell($line, $headers, Column::Time));

            $payment = new Payment(
                $currency,
                abs($amount),
                $this->apiCurrencyValue->getValueInEuro($currency, abs($amount), $time)
            );
            $transactions[$index] = new Transaction(
                $time,
                $this->transactionService->mapType($this->spreadSheetReader->getCell($line, $headers, Column::Type)),
                $income ? $payment : null,
                ! $income ? $payment : null,
            );
            return $transactions;
        }, function ($transactions) use ($tmp) {
            $transactions = $this->transactionService->sortAndMerge($transactions);
            $this->fileManager->save($tmp, $this->jsonFormatter->transactionsToJson($transactions));
        }, $this->fileManager->getValidFileName());

        $this->fileManager->save($tmp, $this->jsonFormatter->closeArray());
        $this->fileManager->printToStdOutput($tmp);
        $this->fileManager->close($tmp);
    }
}