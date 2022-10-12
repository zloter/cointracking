<?php

require 'Types/Transaction.php';

main();

function main()
{
    try {
        $fileName = getValidFileName();
        $inputStream = fopen($fileName, 'r');
        $tmpStream = tmpfile();
        $transactionQueues = [
            'Buy_POS' => [],
            'Buy_NEG' => [],
            'Sell_POS' => [],
            'Sell_NEG' => [],
        ];
        ignoreHeaders($inputStream);
        while ($line = fgetcsv($inputStream)) {
            validateLine($line);
            $income = 0 < $line[5];
            $transaction = new Transaction(
                strtotime($line[1]),
                $line[3],
                $income ? $line[4] : null,
                $income ? $line[5] : null,
                ! $income ? $line[4] : null,
                ! $income ? (-$line[5]) : null
            );
            switch ($transaction->getType()) {
                case 'Buy':
                case 'Sell':
                    $counterpart = array_shift($transactionQueues[$transaction->getType() . "_" . ($income ? "NEG" : "POS")]);
                    if ($counterpart) {
                        if ($income) {
                            $transaction->setSellAmount($counterpart->getSellAmount());
                            $transaction->setSellCurrency($counterpart->getSellCurrency());
                        } else {
                            $transaction->setBuyAmount($counterpart->getBuyAmount());
                            $transaction->setBuyCurrency($counterpart->getBuyCurrency());
                        }
                        fwrite($tmpStream, $transaction->toJson());
                    } else {
                        $transactionQueues[$transaction->getType() . "_" . ($income ?  "POS": "NEG")][] =  $transaction;
                    }
                    break;
                default:
                    fwrite($tmpStream, $transaction->toJson());
            }
        }
        fclose($inputStream);
        outputJson($tmpStream);
        fclose($tmpStream);
    } catch (Exception $e) {
        echo $e->getMessage() . "\n";
    }
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