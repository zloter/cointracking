<?php

namespace Zloter\Cointracking\Types;

use Zloter\Cointracking\Types\TransactionType;
use \Exception;

class Transaction
{

    /**
     * @param int $time
     * @param \Zloter\Cointracking\Types\TransactionType $type
     * @param Payment|null $buyPayment
     * @param Payment|null $sellPayment
     * @throws Exception
     */
    public function __construct(
        private int $time,
        private TransactionType $type,
        private ?Payment $buyPayment,
        private ?Payment $sellPayment
    ) {}

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }
    /**
     * @return TransactionType
     */
    public function getType(): TransactionType
    {
        return $this->type;
    }

    /**
     * @return Payment|null
     */
    public function getBuyPayment(): ?Payment
    {
        return $this->buyPayment;
    }

    /**
     * @return Payment|null
     */
    public function getSellPayment(): ?Payment
    {
        return $this->sellPayment;
    }


    /**
     * @param Transaction $transaction
     * @return void
     */
    public function copyMissingData(Transaction $transaction): void
    {

        $this->buyPayment = $this->buyPayment ?? $transaction->getBuyPayment();
        $this->sellPayment = $this->sellPayment ?? $transaction->getSellPayment();
    }

}