<?php


namespace Zloter\Cointracking\Types;

use \Exception;

class Payment
{
    /**
     * @param string $currency
     * @param float|null $amount
     * @param float|null $euroValue
     * @throws Exception
     */
    public function __construct(
        private string $currency,
        private ?float $amount,
        private ?float $euroValue
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
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @return float|null
     */
    public function getEuroValue(): ?float
    {
        return $this->euroValue;
    }

}