<?php

namespace Zloter\Cointracking\Types;

use Zloter\Cointracking\Types\TransactionType;
use \Exception;

class Transaction
{

    /**
     * @param int $time
     * @param TransactionType $type
     * @param string|null $buyCurrency
     * @param float|null $buyAmount
     * @param string|null $sellCurrency
     * @param float|null $sellAmount
     * @throws Exception
     */
    public function __construct(
        private int             $time,
        private TransactionType $type,
        private ?string         $buyCurrency,
        private ?float          $buyAmount,
        private ?string         $sellCurrency,
        private ?float          $sellAmount,
    )
    {
        if (!empty($buyCurrency) && !ctype_upper($buyCurrency)) {
            throw new Exception("CurrencyNotUppercase $buyCurrency");
        }
        if (!empty($sellCurrency) && !ctype_upper($sellCurrency)) {
            throw new Exception("CurrencyNotUppercase $sellCurrency");
        }
    }

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
     * @return string|null
     */
    public function getBuyCurrency(): ?string
    {
        return $this->buyCurrency;
    }

    /**
     * @return string|null
     */
    public function getBuyAmount(): ?string
    {
        return $this->buyAmount;
    }

    /**
     * @return string|null
     */
    public function getSellCurrency(): ?string
    {
        return $this->sellCurrency;
    }

    /**
     * @return string|null
     */
    public function getSellAmount(): ?string
    {
        return $this->sellAmount;
    }

    /**
     * @param Transaction $transaction
     * @return void
     */
    public function copyMissingData(Transaction $transaction): void
    {
        $this->buyAmount = $this->buyAmount ?? $transaction->getBuyAmount();
        $this->buyCurrency = $this->buyCurrency ?? $transaction->getBuyCurrency();
        $this->sellAmount = $this->sellAmount ?? $transaction->getSellAmount();
        $this->sellCurrency = $this->sellCurrency ?? $transaction->getSellCurrency();
    }

}