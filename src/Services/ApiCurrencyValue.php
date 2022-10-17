<?php

namespace Zloter\Cointracking\Services;

use Codenixsv\CoinGeckoApi\CoinGeckoClient;


class ApiCurrencyValue
{
    /**
     * @var CoinGeckoClient
     */
    private CoinGeckoClient $client;

    public function __construct() {
        $this->client = new CoinGeckoClient();
    }

    /**
     * @param string $currency
     * @param float $amount
     * @param int $timestamp
     * @return float
     * @throws \Exception
     */
    public function getValueInEuro(string $currency, float $amount, int $timestamp): float
    {
        if ("EUR" === $currency) {
            return $amount;
        }
        $range = $this->findInterval($timestamp);
        $data = $this->client->coins()->getMarketChartRange(
            $this->currencyMapping($currency),
            'eur',
            $timestamp - $range,
            $timestamp + $range
        );

        sleep(6); // free api allows only 10-50 request per second so we are limiting it greatly

        $price = $this->findClosest($data["prices"], $timestamp);
        return $price * $amount;
    }

    /**
     * @param string $currency
     * @return string
     * @throws \Exception
     */
    private function currencyMapping(string $currency): string
    {
        return match($currency) {
            "ETH" => "ethereum", // two option, assuming ethereum
            "BNB" => "binancecoin", // assuming binancecoin
            "LAZIO" => "lazio-fan-token",
            "BTC" => "bitcoin",
            "AVAX" => "avalanche-2", // has three representations and is not unique; assuming
            "DAR" => "mines-of-dalarnia",
            default => throw new \Exception("Unmapped currency $currency")
        };
    }

    /**
     * Coingecko keep data with interval of 5 minutes from one day
     * from 90 days with interval of one hour
     * and from one day for the rest
     * @param int $timestamp
     * @return float|int
     */
    private function findInterval(int $timestamp)
    {
        $timePassed = time() - $timestamp;
        if ($timePassed < 24 * 60 * 60) {
            return 5 * 60 / 2;
        }
        if ($timePassed < 90 * 24 * 60 * 60) {
            return 60 * 60 / 3;
        }
        return 24 * 60 * 60 / 2;
    }

    /**
     * Find closest timestamp
     *
     * @param array $values
     * @param int $timestamp
     * @return mixed
     */
    private function findClosest(array $values, int $timestamp)
    {
        $index = null;
        foreach ($values as $key => $value) {
            if ($value[0] > $timestamp) {
                $index = $key;
                break;
            }
        }
        if ($index === 0) {
            return $values[0][1];
        }
        if ($index > 0) {
            if (abs($values[$index - 1] - $timestamp) > abs($values[$index] - $timestamp)) {
                return $values[$index];
            } else {
                return $values[$index - 1];
            }
        } else {
            return end($values)[1] ?? 0;
        }
    }
}