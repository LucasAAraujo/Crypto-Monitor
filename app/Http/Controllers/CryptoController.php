<?php

namespace App\Http\Controllers;

use App\Services\CoinGeckoService;
use Illuminate\Http\Request;

class CryptoController extends Controller
{
    protected $coinGeckoService;

    public function __construct(CoinGeckoService $coinGeckoService)
    {
        $this->coinGeckoService = $coinGeckoService;
    }

    /**
     * Exibe a página inicial com o gráfico e painéis
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $topCoins = $this->coinGeckoService->getTopCoins(20);
        $topGainers = $this->coinGeckoService->getTopGainers();
        $topLosers = $this->coinGeckoService->getTopLosers();
        
        return view('crypto.index', compact('topCoins', 'topGainers', 'topLosers'));
    }

    /**
     * Obtém a lista de criptomoedas disponíveis
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCoins()
    {
        $coins = $this->coinGeckoService->getCoins();
        return response()->json($coins);
    }

    /**
     * Obtém os dados históricos de preço de uma criptomoeda
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistoricalData(Request $request)
    {
        $coinId = $request->input('coin_id', 'bitcoin');
        $interval = $request->input('interval', '1d');
        
        $data = $this->coinGeckoService->getHistoricalData($coinId, $interval);
        return response()->json($data);
    }

    /**
     * Obtém os dados OHLC para gráficos de velas
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOHLCData(Request $request)
    {
        $coinId = $request->input('coin_id', 'bitcoin');
        $interval = $request->input('interval', '1d');
        
        $data = $this->coinGeckoService->getOHLCData($coinId, $interval);
        return response()->json($data);
    }

    /**
     * Obtém as 5 criptomoedas com maior valorização nas últimas 24h
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTopGainers()
    {
        $topGainers = $this->coinGeckoService->getTopGainers();
        return response()->json($topGainers);
    }

    /**
     * Obtém as 5 criptomoedas com maior desvalorização nas últimas 24h
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTopLosers()
    {
        $topLosers = $this->coinGeckoService->getTopLosers();
        return response()->json($topLosers);
    }
}
