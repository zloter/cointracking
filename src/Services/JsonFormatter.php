<?php

namespace Zloter\Cointracking\Services;

use Zloter\Cointracking\Types\Transaction;

class JsonFormatter
{
    public function openArray()
    {
        return "[\n";
    }

    public function closeArray()
    {
        return "\n]\n";
    }

    /**
     * @param Transaction[] $transactions
     * @return string
     */
    function transactionsToJson(array $transactions): string
    {
        $content = '';
        foreach ($transactions as $transaction) {
            $arr = [];
            $arr['time'] = $transaction->getTime();
            $arr['type'] = $transaction->getType();
            if ($transaction->getBuyCurrency()) {
                $arr['buy_currency'] = $transaction->getBuyCurrency();
            }
            if ($transaction->getBuyAmount()) {
                $arr['buy'] = (float) $transaction->getBuyAmount();
            }
            if ($transaction->getSellCurrency()) {
                $arr['sell_currency'] = $transaction->getSellCurrency();
            }
            if ($transaction->getSellAmount()) {
                $arr['sell'] = (float) $transaction->getSellAmount();
            }
            $content .= json_encode($arr, JSON_PRETTY_PRINT);
        }
        return $content;
    }
}