<?php

require_once "vendor/autoload.php";

use Zloter\Cointracking\Types\Column;
use Zloter\Cointracking\Types\Transaction;
use Zloter\Cointracking\Types\TransactionType;

main();

function main()
{
    date_default_timezone_set("Europe/Warsaw"); // to ease to comparison
    try {
        $fileName = getValidFileName();
        $inputStream = fopen($fileName, 'r');
        $tmpStream = tmpfile();
        $uncompletedIndexes = [
            'POS' => [],
            'NEG' => [],
        ];
        $transactions = [];
        $index = 0;
        $currentTimestamp = 0;
        $headers = mapHeaders(fgetcsv($inputStream));
        while ($line = fgetcsv($inputStream)) {
            if ($currentTimestamp !== strtotime($line[1])) {
                fwrite($tmpStream, processToJson($transactions));
                $transactions = [];
            }
            $currentTimestamp = strtotime(getCell($line, $headers, Column::Time));
            $index++;
            $income = 0 < getCell($line, $headers, Column::Amount);
            $transactions[$index] = new Transaction(
                strtotime(getCell($line, $headers, Column::Time)),
                mapType(getCell($line, $headers, Column::Type)),
                $income ? getCell($line, $headers, Column::Currency) : null,
                $income ? getCell($line, $headers, Column::Amount) : null,
                ! $income ? getCell($line, $headers, Column::Currency) : null,
                ! $income ? (-getCell($line, $headers, Column::Amount)) : null
            );
            switch ($transactions[$index]->getType()) {
                case TransactionType::TRADE:
                    $counterpartIndex = array_shift($uncompletedIndexes[($income ? "NEG" : "POS")]);
                    if ($counterpartIndex) {
                        $transactions[$counterpartIndex]->copyMissingData($transactions[$index]);
                        unset($transactions[$index]);
                    } else {
                        $uncompletedIndexes[($income ?  "POS": "NEG")][] =  $index;
                    }
            }
        }
        fwrite($tmpStream, processToJson($transactions));
        fclose($inputStream);
        outputJson($tmpStream);
        fclose($tmpStream);
    } catch (Exception $e) {
        echo $e->getMessage() . "\n" . $e->getLine() . "\n ";
    }
}

function mapType(mixed $type) :TransactionType
{
    switch ($type) {
        case "Sell":
        case "Buy":
            return TransactionType::TRADE;
        case "Fee":
            return TransactionType::FEE;
        case "Referral Kickback":
            return TransactionType::REWARD;
        case "Deposit":
            return TransactionType::DEPOSIT;
        case "Super BNB Mining":
            return TransactionType::MINING;
        default:
            throw new Exception("UndefinedTransactionType: $type");
    }
}

function processToJson(array $transactions): string
{
    $content = '';
    $trades = array_filter($transactions, function (Transaction $t) {
        return $t->getType() === TransactionType::TRADE;
    });
    $fees = array_filter($transactions, function (Transaction $t) {
        return $t->getType() === TransactionType::FEE;
    });
    $others = array_filter($transactions, function (Transaction $t) {
        return ! in_array($t->getType(), [TransactionType::FEE, TransactionType::TRADE]);
    });
    while(count($trades)) {
        $content .= array_shift($trades)->toJson();
        if (count($fees))$content .= array_shift($fees)->toJson();
    }
    foreach($fees as $fee) {
        echo "there are some";
        $content .= $fee->toJson();
    }
    foreach($others as $other) {
        $content .= $other->toJson();
    }
    return $content;
}

function mapHeaders(array $headers)
{
    return [
        Column::Time->value => array_search("UTC_Time", $headers),
        Column::Type->value => array_search("Operation", $headers),
        Column::Operation->value => array_search("Operation", $headers),
        Column::Currency->value => array_search("Coin", $headers),
        Column::Amount->value => array_search("Change", $headers)
    ];
}

function getCell(array $line, array $headers, Column $column)
{
    return $line[$headers[$column->value]];
}

/**
 * @return string
 * @throws Exception
 */
function getValidFileName(): string
{
    $fileName = $_SERVER['argv'][1] ?? null;
    if (! $fileName) {
        throw new Exception('NoInputFile');
    }
    $file_extension = pathinfo($fileName, PATHINFO_EXTENSION);
    if (! in_array($file_extension, ['csv'])) {
        throw new Exception('InvalidFileType');
    }
    if (! is_readable($fileName)) {
        throw new Exception('FileNotExistsOrUnreadable');
    }
    return $fileName;
}

/**
 * For now for ease of debbuging pretty print
 */
function outputJson($stream)
{
    echo "[\n";
    fseek($stream, 0);
    while($line = fgets($stream)) {
        if ($line === "}{\n") {
            echo "  },  \n  {\n";
        } else {
            echo "  $line";
        }
    }
    echo "\n]\n";
}