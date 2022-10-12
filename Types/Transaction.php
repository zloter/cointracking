<?php

class Transaction
{
    /**
     * @param int $time
     * @param string $type
     * @param string|null $buyCurrency
     * @param string|null $buyAmount
     * @param string|null $sellCurrency
     * @param string|null $sellAmount
     */
    public function __construct(
        private int $time,
        private string $type,
        private ?string $buyCurrency,
        private ?float $buyAmount,
        private ?string $sellCurrency,
        private ?float $sellAmount,
    ) {}

    /**
     * @param string|null $buyCurrency
     */
    public function setBuyCurrency(?string $buyCurrency): void
    {
        $this->buyCurrency = $buyCurrency;
    }

    /**
     * @param string|null $buyAmount
     */
    public function setBuyAmount(?string $buyAmount): void
    {
        $this->buyAmount = $buyAmount;
    }

    /**
     * @param string|null $sellCurrency
     */
    public function setSellCurrency(?string $sellCurrency): void
    {
        $this->sellCurrency = $sellCurrency;
    }

    /**
     * @param string|null $sellAmount
     */
    public function setSellAmount(?string $sellAmount): void
    {
        $this->sellAmount = $sellAmount;
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getType(): string
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

    public function toJson()
    {
        $arr = [];
        $arr['time'] = $this->time;
        $arr['type'] = $this->type;
        if ($this->buyCurrency) {
            $arr['buy_currency'] = $this->buyCurrency;
        }
        if ($this->buyAmount) {
            $arr['buy'] = $this->buyAmount;
        }
        if ($this->sellCurrency) {
            $arr['sell_currency'] = $this->sellCurrency;
        }
        if ($this->sellAmount) {
            $arr['sell'] = $this->sellAmount;
        }
        return json_encode($arr, JSON_PRETTY_PRINT);
    }
}