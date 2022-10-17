<?php

namespace Zloter\Cointracking\Services;

use Zloter\Cointracking\Types\Column;
use Zloter\Cointracking\Types\Transaction;
use Zloter\Cointracking\Types\TransactionType;

class TransactionService
{
    /**
     * @param Transaction[] $transactions
     * @return Transaction[]
     */
    public function sortAndMerge(array $transactions): array
    {
        $uncompletedIndexes = [
            'POS' => [],
            'NEG' => [],
        ];
        foreach($transactions as $index => $transaction) {
            $income = null === $transaction->getSellAmount();
            if ($transaction->getType() === TransactionType::TRADE) {
                $counterpartIndex = array_shift($uncompletedIndexes[($income ? "NEG" : "POS")]);
                if ($counterpartIndex) {
                    $transactions[$counterpartIndex]->copyMissingData($transaction);
                    unset($transactions[$index]);
                } else {
                    $uncompletedIndexes[($income ? "POS" : "NEG")][] = $index;
                }
            }
        }
        $sorted = [];
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
            $sorted[] = array_shift($trades);
            if (count($fees)) {
                $sorted[] = array_shift($fees);
            }
        }
        foreach($fees as $fee) {
            $sorted[] = $fee;
        }
        foreach($others as $other) {
            $sorted[] = $other;
        }
        return $sorted;
    }

    /**
     * @param mixed $type
     * @return TransactionType
     * @throws \Exception
     */
    public function mapType(mixed $type) :TransactionType
    {
        return match ($type) {
            "Sell", "Buy" => TransactionType::TRADE,
            "Fee" => TransactionType::FEE,
            "Referral Kickback" => TransactionType::REWARD,
            "Deposit" => TransactionType::DEPOSIT,
            "Super BNB Mining" => TransactionType::MINING,
            default => throw new \Exception("UndefinedTransactionType: $type")
        };
    }
}