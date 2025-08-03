<x-app-layout>
    <div class="max-w-4xl mx-auto py-6 px-4 space-y-6">
        <h1 class="text-2xl font-bold">📊 Dashboard Analytics</h1>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div class="bg-white shadow p-4 rounded">
                <p class="text-gray-500">Total Dresses</p>
                <p class="text-xl font-bold">{{ $total }}</p>
            </div>
            <div class="bg-white shadow p-4 rounded">
                <p class="text-gray-500">Sold</p>
                <p class="text-xl font-bold text-red-600">{{ $sold }}</p>
            </div>
            <div class="bg-white shadow p-4 rounded">
                <p class="text-gray-500">Available</p>
                <p class="text-xl font-bold text-green-600">{{ $available }}</p>
            </div>
            <div class="bg-white shadow p-4 rounded">
                <p class="text-gray-500">Capital in Stock</p>
                <p class="text-xl font-bold">Ksh {{ number_format($capital, 0) }}</p>
            </div>
            <div class="bg-white shadow p-4 rounded">
                <p class="text-gray-500">Expected Revenue</p>
                <p class="text-xl font-bold">Ksh {{ number_format($expectedRevenue, 0) }}</p>
            </div>
            <div class="bg-white shadow p-4 rounded">
                <p class="text-gray-500">Total Profit</p>
                <p class="text-xl font-bold text-blue-700">Ksh {{ number_format($profit, 0) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
            <div class="bg-blue-100 shadow p-4 rounded">
                <p class="text-blue-800 font-semibold">📅 Today</p>
                <p class="text-lg font-bold text-blue-900">Ksh {{ number_format($dailyProfit, 0) }}</p>
            </div>
            <div class="bg-green-100 shadow p-4 rounded">
                <p class="text-green-800 font-semibold">🗓️ This Week</p>
                <p class="text-lg font-bold text-green-900">Ksh {{ number_format($weeklyProfit, 0) }}</p>
            </div>
            <div class="bg-purple-100 shadow p-4 rounded">
                <p class="text-purple-800 font-semibold">📆 This Month</p>
                <p class="text-lg font-bold text-purple-900">Ksh {{ number_format($monthlyProfit, 0) }}</p>
            </div>
        </div>
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-4 rounded shadow">
                <h2 class="font-semibold mb-2">📊 Dresses Sold Per Day</h2>
                <canvas id="salesChart" height="200"></canvas>
            </div>

            <div class="bg-white p-4 rounded shadow">
                <h2 class="font-semibold mb-2">📈 Daily Profit Trend</h2>
                <canvas id="profitChart" height="200"></canvas>
            </div>
        </div>
    </div>

    

    <script>
async function initializeCharts() {
    // Ensure Chart.js is loaded
    const Chart = await window.loadChartJS();
    
    const salesCtx = document.getElementById('salesChart');
    const profitCtx = document.getElementById('profitChart');

    // Optimize chart configuration for better performance
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 750, // Reduced animation time
        },
        interaction: {
            intersect: false,
        },
        plugins: {
            legend: {
                display: false, // Hide legend to save space
            }
        },
        scales: {
            y: { 
                beginAtZero: true,
                grid: {
                    display: false, // Remove grid for cleaner look
                }
            },
            x: {
                grid: {
                    display: false,
                }
            }
        }
    };

    const salesChart = new Chart(salesCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($dailySales->pluck('date')->values()) !!},
            datasets: [{
                label: 'Dresses Sold',
                data: {!! json_encode($dailySales->pluck('count')->values()) !!},
                backgroundColor: '#4f46e5',
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            ...defaultOptions,
            scales: {
                ...defaultOptions.scales,
                y: {
                    ...defaultOptions.scales.y,
                    ticks: {
                        stepSize: 1, // Ensure integer steps for dress count
                    }
                }
            }
        }
    });

    const profitChart = new Chart(profitCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyProfitData->pluck('date')->values()) !!},
            datasets: [{
                label: 'Daily Profit (Ksh)',
                data: {!! json_encode($dailyProfitData->pluck('profit')->values()) !!},
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 5,
            }]
        },
        options: defaultOptions
    });
}

// Initialize charts when DOM is ready and Chart.js is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCharts);
} else {
    initializeCharts();
}
</script>



</x-app-layout>
