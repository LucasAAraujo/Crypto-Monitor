@extends('layouts.app')

@section('title', 'Dashboard de Criptomoedas')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Painel principal com gráfico -->
    <div class="w-4/5 mx-auto bg-white rounded-lg shadow-md p-4">
        <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between">
            <h2 class="text-xl font-bold text-gray-800 mb-2 md:mb-0">Gráfico de Preços</h2>
            <div class="flex flex-col w-2/5 md:flex-row space-y-2 md:space-y-0 md:space-x-2">
                <select id="coin-selector" class="rounded-md border-gray-300 mb-5 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    @foreach($topCoins as $coin)
                        <option value="{{ $coin['id'] }}" {{ $coin['id'] === 'bitcoin' ? 'selected' : '' }}>
                            {{ $coin['name'] }} ({{ strtoupper($coin['symbol']) }})
                        </option>
                    @endforeach
                </select>
                
                <!-- Seleção de intervalo de tempo -->
                <select id="time-interval" class="rounded-md border-gray-300 mb-5 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="1d">1 dia</option>
                    <option value="7d">1 semana</option>
                    <option value="30d">1 mês</option>
                    <option value="365d">1 ano</option>
                </select>
                
                <!-- Toggle para tipo de gráfico -->
                <!-- <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600">Linha</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="chart-type-toggle" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        <span class="text-sm text-gray-600">Velas</span>
                    </label>
                </div> -->
            </div>
        <div class="h-80 w-3/4">
            <canvas id="price-chart"></canvas>
        </div>
    </div>
    
    <!-- Painel lateral com variações -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-md p-4">
        <div class="mb-4">
            <h2 class="text-xl font-bold text-gray-800">Variação em 24h</h2>
            <p class="text-sm text-gray-500">Atualização automática a cada 10 segundos</p>
        </div>
        
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-green-600 mb-2">Maiores Valorizações</h3>
            <div id="top-gainers" class="space-y-2">
                @foreach($topGainers as $coin)
                <div class="flex items-center justify-between p-2 border-b border-gray-100">
                    <div class="flex items-center">
                        <img src="{{ $coin['image'] }}" alt="{{ $coin['name'] }}" class="w-6 h-6 mr-2">
                        <span class="font-medium">{{ $coin['name'] }}</span>
                        <span class="text-gray-500 text-sm ml-1">({{ strtoupper($coin['symbol']) }})</span>
                    </div>
                    <div class="text-green-600 font-semibold">
                        +{{ number_format($coin['price_change_percentage_24h'], 2) }}%
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <div>
            <h3 class="text-lg font-semibold text-red-600 mb-2">Maiores Desvalorizações</h3>
            <div id="top-losers" class="space-y-2">
                @foreach($topLosers as $coin)
                <div class="flex items-center justify-between p-2 border-b border-gray-100">
                    <div class="flex items-center">
                        <img src="{{ $coin['image'] }}" alt="{{ $coin['name'] }}" class="w-6 h-6 mr-2">
                        <span class="font-medium">{{ $coin['name'] }}</span>
                        <span class="text-gray-500 text-sm ml-1">({{ strtoupper($coin['symbol']) }})</span>
                    </div>
                    <div class="text-red-600 font-semibold">
                        {{ number_format($coin['price_change_percentage_24h'], 2) }}%
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let priceChart;
        let chartType = 'line';
        let selectedCoin = document.getElementById('coin-selector').value;
        let selectedInterval = document.getElementById('time-interval').value;
        
        initChart();
        
        document.getElementById('coin-selector').addEventListener('change', function() {
            selectedCoin = this.value;
            updateChart();
        });
        
        document.getElementById('time-interval').addEventListener('change', function() {
            selectedInterval = this.value;
            updateChart();
        });
        
        setInterval(updateVariationPanels, 10000);
        
        // Funções
        function initChart() {
            const ctx = document.getElementById('price-chart').getContext('2d');
            
            priceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Preço (USD)',
                        data: [],
                        borderColor: 'rgb(79, 70, 229)',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        borderWidth: 2,
                        pointRadius: 0,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: 'day'
                            }
                        },
                        y: {
                            beginAtZero: false
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        tooltip: {
                            enabled: true
                        },
                        legend: {
                            display: true
                        }
                    }
                }
            });
            
            updateChart();
        }
        
        function updateChart() {
            const endpoint = '/api/crypto/historical';
            
            fetch(`${endpoint}?coin_id=${selectedCoin}&interval=${selectedInterval}`)
                .then(response => response.json())
                .then(data => {
                    updateLineChart(data);
                })
                .catch(error => console.error('Erro ao carregar dados do gráfico:', error));
        }
        
        function updateLineChart(data) {
            if (priceChart) {
                priceChart.destroy();
            }
            
            const ctx = document.getElementById('price-chart').getContext('2d');
            const prices = data.prices || [];
            
            const chartData = prices.map(item => ({
                x: new Date(item[0]),
                y: item[1]
            }));
            
            priceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Preço (USD)',
                        data: chartData,
                        borderColor: 'rgb(79, 70, 229)',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        borderWidth: 2,
                        pointRadius: 0,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                unit: getTimeUnit(selectedInterval)
                            }
                        },
                        y: {
                            beginAtZero: false
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Preço: $${context.parsed.y.toFixed(2)}`;
                                }
                            }
                        }
                    }
                }
            });
        }
        
        function getTimeUnit(interval) {
            switch (interval) {
                case '1d':
                    return 'hour';
                case '7d':
                    return 'day';
                case '30d':
                    return 'day';
                case '365d':
                    return 'month';
                default:
                    return 'day';
            }
        }
        
        function updateVariationPanels() {
            fetch('/api/crypto/top-gainers')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('top-gainers');
                    container.innerHTML = '';
                    
                    data.forEach(coin => {
                        const item = document.createElement('div');
                        item.className = 'flex items-center justify-between p-2 border-b border-gray-100';
                        item.innerHTML = `
                            <div class="flex items-center">
                                <img src="${coin.image}" alt="${coin.name}" class="w-6 h-6 mr-2">
                                <span class="font-medium">${coin.name}</span>
                                <span class="text-gray-500 text-sm ml-1">(${coin.symbol.toUpperCase()})</span>
                            </div>
                            <div class="text-green-600 font-semibold">
                                +${coin.price_change_percentage_24h.toFixed(2)}%
                            </div>
                        `;
                        container.appendChild(item);
                    });
                })
                .catch(error => console.error('Erro ao atualizar top gainers:', error));
            
            // Atualizar Top Losers
            fetch('/api/crypto/top-losers')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('top-losers');
                    container.innerHTML = '';
                    
                    data.forEach(coin => {
                        const item = document.createElement('div');
                        item.className = 'flex items-center justify-between p-2 border-b border-gray-100';
                        item.innerHTML = `
                            <div class="flex items-center">
                                <img src="${coin.image}" alt="${coin.name}" class="w-6 h-6 mr-2">
                                <span class="font-medium">${coin.name}</span>
                                <span class="text-gray-500 text-sm ml-1">(${coin.symbol.toUpperCase()})</span>
                            </div>
                            <div class="text-red-600 font-semibold">
                                ${coin.price_change_percentage_24h.toFixed(2)}%
                            </div>
                        `;
                        container.appendChild(item);
                    });
                })
                .catch(error => console.error('Erro ao atualizar top losers:', error));
        }
    });
</script>
@endpush
