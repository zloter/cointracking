<?php

require 'Types/Transaction.php';

main();

function main()
{
    try {
        $fileName = getValidFileName();
        $inputStream = fopen($fileName, 'r');
        $tmpStream = tmpfile();
        $uncompletedIndexes = [
            'Buy_POS' => [],
            'Buy_NEG' => [],
            'Sell_POS' => [],
            'Sell_NEG' => [],
        ];
        $transactions = [];
        $index = 0;
        $currentTimestamp = 0;
        ignoreHeaders($inputStream);
        while ($line = fgetcsv($inputStream)) {
            if ($currentTimestamp !== strtotime($line[1])) {
                $content = processToJson($transactions);
                fwrite($tmpStream, $content);
                $transactions = [];
            }
            $currentTimestamp = strtotime($line[1]);
            $index++;
            validateLine($line);
            $income = 0 < $line[5];
            $transactions[$index] = new Transaction(
                strtotime($line[1]),
                $line[3],
                $income ? $line[4] : null,
                $income ? $line[5] : null,
                ! $income ? $line[4] : null,
                ! $income ? (-$line[5]) : null
            );
            switch ($transactions[$index]->getType()) {
                case 'Buy':
                case 'Sell':
                    $counterpartIndex = array_shift($uncompletedIndexes[$transactions[$index]->getType() . "_" . ($income ? "NEG" : "POS")]);
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
                        $uncompletedIndexes[$transactions[$index]->getType() . "_" . ($income ?  "POS": "NEG")][] =  $index;
                    }
            }
        }
        fclose($inputStream);
        outputJson($tmpStream);
        fclose($tmpStream);
    } catch (Exception $e) {
        echo $e->getMessage() . "\n";
    }
}

function processToJson(array $transactions): string
{
    $content = '';
    $trades = array_filter($transactions, function (Transaction $t) {
        return in_array($t->getType(), ["Buy", "Sell"]);
    });
    $fees = array_filter($transactions, function (Transaction $t) {
        return in_array($t->getType(), ["Fee"]);
    });
    $others = array_filter($transactions, function (Transaction $t) {
        return !in_array($t->getType(), ["Fee", "Buy", "Sell"]);
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

function generateKey(Transaction $transaction)
{
    return $transaction->getTime() . "_" . $transaction->getType();
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