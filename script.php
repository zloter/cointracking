<?php

require_once 'Types/Transaction.php';
require_once 'Types/TransactionType.php';

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
        ignoreHeaders($inputStream);
        while ($line = fgetcsv($inputStream)) {
            if ($currentTimestamp !== strtotime($line[1])) {
                fwrite($tmpStream, processToJson($transactions));
                $transactions = [];
            }
            $currentTimestamp = strtotime($line[1]);
            $index++;
            validateLine($line);
            $income = 0 < $line[5];
            $transactions[$index] = new Transaction(
                strtotime($line[1]),
                mapType($line[3]),
                $income ? $line[4] : null,
                $income ? $line[5] : null,
                ! $income ? $line[4] : null,
                ! $income ? (-$line[5]) : null
            );
            switch ($transactions[$index]->getType()) {
                case TransactionType::TRADE:
                    $counterpartIndex = array_shift($uncompletedIndexes[($income ? "NEG" : "POS")]);
                    if ($counterpartIndex) {
                        if ($income) {
                            $transactions[$counterpartIndex]->setBuyAmount($transactions[$index]->getBuyAmount());
                            $transactions[$counterpartIndex]->setBuyCurrency($transactions[$index]->getBuyCurrency());
                        } else {
                            $transactions[$counterpartIndex]->setSellAmount($transactions[$index]->getSellAmount());
                            $transactions[$counterpartIndex]->setSellCurrency($transactions[$index]->getSellCurrency());
                        }
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
        echo $e->getMessage() . "\n";
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

function ignoreHeaders($stream)
{
    fgetcsv($stream);
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


function validateLine(array $line)
{
    return null;
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