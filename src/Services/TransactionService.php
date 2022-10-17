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
            $income = null === $transaction->getSellPayment();
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
        return $this->sort($transactions);
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

    /**
     * Assumed order of transaction with same timestamp
     * All pairs (Trade, Fee to Trade), bonuses
     *
     * To fit fee with trade I'm using dolar value
     * @param Transaction[] $transactions
     * @return Transaction[]
     */
    private function sort(array $transactions): array
    {
        $sorted = [];
        $trades = array_values(array_filter($transactions, function (Transaction $t) {
            return $t->getType() === TransactionType::TRADE;
        }));
        $fees = array_values(array_filter($transactions, function (Transaction $t) {
            return $t->getType() === TransactionType::FEE;
        }));
        $others = array_values(array_filter($transactions, function (Transaction $t) {
            return ! in_array($t->getType(), [TransactionType::FEE, TransactionType::TRADE]);
        }));

        $tradeRanks = $this->rankBySellPrices($trades);
        $feesRanks = $this->rankBySellPrices($fees);

        foreach($trades as $key => $trade) {
            $sorted[] = $trade;
            $sorted[] = $fees[array_search($tradeRanks[$key], $feesRanks)];
            unset($fees[array_search($tradeRanks[$key], $feesRanks)]);
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
     * @param Transaction[] $trades
     * @return array ranks index -> order
     */
    private function rankBySellPrices(array $transactions): array
    {
        $prices = array_reduce($transactions, function ($carry, $transaction) {
            $carry[] = $transaction->getSellPayment()->getEuroValue();
            return $carry;
        }) ?? [];
        $ranks = $prices;
        sort($prices);
        foreach ($ranks as $index => $value) {
            $ranks[$index] = array_search($value, $prices);
        }
        return $ranks;
    }
}