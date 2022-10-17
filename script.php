<?php

require_once "vendor/autoload.php";

use Zloter\Cointracking\Reader\ReaderFactory;
use Zloter\Cointracking\Services\FileManager;
use Zloter\Cointracking\Services\JsonFormatter;
use Zloter\Cointracking\Services\TransactionService;
use Zloter\Cointracking\Commands\ProcessTransactionSheetToJson;
use Zloter\Cointracking\Services\ApiCurrencyValue;
main();

function main()
{
    try {
        date_default_timezone_set("Europe/Warsaw"); // to ease to comparison

        $fileManager = new FileManager();
        $transactionService = new TransactionService();
        $jsonFormatter = new JsonFormatter();

        $ext = $fileManager->getExtension($fileManager->getValidFileName());
        $reader = ReaderFactory::createReader($ext);

        $api = new ApiCurrencyValue();

        (new ProcessTransactionSheetToJson($fileManager, $transactionService, $jsonFormatter, $reader, $api))();

    } catch (\Exception $e) {
        echo $e->getMessage() . "\nLine: " . $e->getLine() . "\nFile: " . $e->getFile() . "\n";
    }
}
