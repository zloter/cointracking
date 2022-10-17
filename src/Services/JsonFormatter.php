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
            if ($transaction->getBuyPayment()) {
                $arr['buy_currency'] = $transaction->getBuyPayment()->getCurrency();
                $arr['buy'] = (float) $transaction->getBuyPayment()->getAmount();
                $arr['buy_eur'] = (float) $transaction->getBuyPayment()->getEuroValue();

            }
            if ($transaction->getSellPayment()) {
                $arr['sell_currency'] = $transaction->getSellPayment()->getCurrency();
                $arr['sell'] = (float) $transaction->getSellPayment()->getAmount();
                $arr['sell_eur'] = (float) $transaction->getSellPayment()->getEuroValue();
            }
            $content .= json_encode($arr, JSON_PRETTY_PRINT);
        }
        return $content;
    }
}