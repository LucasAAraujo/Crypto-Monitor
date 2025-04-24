<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CoinGeckoService
{
    protected $baseUrl = 'https://api.coingecko.com/api/v3';
    protected $cacheTime = 60;

    /**
     * Obtém a lista de criptomoedas disponíveis
     *
     * @return array
     */
    public function getCoins()
    {
        return Cache::remember('coins_list', 3600, function () {
            $response = Http::get("{$this->baseUrl}/coins/list");
            return $response->json();
        });
    }

    /**
     * Obtém os dados de mercado das principais criptomoedas
     *
     * @param int $count Número de criptomoedas a serem retornadas
     * @return array
     */
    public function getTopCoins($count = 100)
    {
        return Cache::remember('top_coins', $this->cacheTime, function () use ($count) {
            $response = Http::get("{$this->baseUrl}/coins/markets", [
                'vs_currency' => 'usd',
                'order' => 'market_cap_desc',
                'per_page' => $count,
                'page' => 1,
                'sparkline' => false,
                'price_change_percentage' => '24h'
            ]);
            return $response->json();
        });
    }

    /**
     * Obtém os dados históricos de preço de uma criptomoeda
     *
     * @param string $coinId ID da criptomoeda
     * @param string $interval Intervalo de tempo (1d, 7d, 30d, 365d)
     * @return array
     */
    public function getHistoricalData($coinId, $interval)
    {
        $days = $this->intervalToDays($interval);
        
        return Cache::remember("historical_{$coinId}_{$interval}", $this->cacheTime * 5, function () use ($coinId, $days) {
            $response = Http::get("{$this->baseUrl}/coins/{$coinId}/market_chart", [
                'vs_currency' => 'usd',
                'days' => $days,
            ]);
            return $response->json();
        });
    }

    /**
     * Obtém os dados OHLC (Open, High, Low, Close) para gráficos de velas
     *
     * @param string $coinId ID da criptomoeda
     * @param string $interval Intervalo de tempo (1d, 7d, 30d, 365d)
     * @return array
     */
    public function getOHLCData($coinId, $interval)
    {
        $days = $this->intervalToDays($interval);
        
        return Cache::remember("ohlc_{$coinId}_{$interval}", $this->cacheTime * 5, function () use ($coinId, $days) {
            $response = Http::get("{$this->baseUrl}/coins/{$coinId}/ohlc", [
                'vs_currency' => 'usd',
                'days' => $days,
            ]);
            return $response->json();
        });
    }

    /**
     * Obtém as 5 criptomoedas com maior valorização nas últimas 24h
     *
     * @return array
     */
    public function getTopGainers()
    {
        return Cache::remember('top_gainers', $this->cacheTime, function () {
            $coins = $this->getTopCoins(250);
            usort($coins, function ($a, $b) {
                return $b['price_change_percentage_24h'] <=> $a['price_change_percentage_24h'];
            });
            
            return array_slice($coins, 0, 5);
        });
    }

    /**
     * Obtém as 5 criptomoedas com maior desvalorização nas últimas 24h
     *
     * @return array
     */
    public function getTopLosers()
    {
        return Cache::remember('top_losers', $this->cacheTime, function () {
            $coins = $this->getTopCoins(250);
            usort($coins, function ($a, $b) {
                return $a['price_change_percentage_24h'] <=> $b['price_change_percentage_24h'];
            });
            
            return array_slice($coins, 0, 5);
        });
    }

    /**
     * Converte o intervalo de tempo em dias para a API
     *
     * @param string $interval Intervalo (1d, 7d, 30d, 365d)
     * @return int
     */
    private function intervalToDays($interval)
    {
        switch ($interval) {
            case '1d':
                return 1;
            case '7d':
                return 7;
            case '30d':
                return 30;
            case '365d':
                return 365;
            default:
                return 1;
        }
    }
}
