<?php

namespace App\Http\Controllers;

use App\Models\Dress;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        $total = Dress::sum('original_quantity');
        $available = Dress::sum('quantity');
        $sold = $total - $available;

        $capital = Dress::sum(DB::raw('quantity * buying_price'));
        $revenue = Dress::sum(DB::raw('quantity * selling_price'));
        $expectedRevenue = Dress::sum(DB::raw('original_quantity * selling_price'));

        $costOfGoodsSold = Dress::sum(DB::raw('(original_quantity - quantity) * buying_price'));
        $revenueSold = Dress::sum(DB::raw('(original_quantity - quantity) * selling_price'));
        $profit = $revenueSold - $costOfGoodsSold;


        // Daily Profit
        $dailyProfit = Sale::join('dresses', 'sales.dress_id', '=', 'dresses.id')
            ->whereDate('sales.sold_at', now())
            ->selectRaw('SUM((dresses.selling_price - dresses.buying_price) * sales.quantity) as profit')
            ->value('profit') ?? 0;

        // Weekly Profit
        $weeklyProfit = Sale::join('dresses', 'sales.dress_id', '=', 'dresses.id')
            ->whereBetween('sales.sold_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->selectRaw('SUM((dresses.selling_price - dresses.buying_price) * sales.quantity) as profit')
            ->value('profit') ?? 0;

        // Monthly Profit
        $monthlyProfit = Sale::join('dresses', 'sales.dress_id', '=', 'dresses.id')
            ->whereMonth('sales.sold_at', now()->month)
            ->selectRaw('SUM((dresses.selling_price - dresses.buying_price) * sales.quantity) as profit')
            ->value('profit') ?? 0;


        $dailySales = Sale::selectRaw('DATE(sold_at) as date, SUM(quantity) as count')
            ->where('sold_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyProfitData = Sale::join('dresses', 'sales.dress_id', '=', 'dresses.id')
            ->selectRaw('DATE(sales.sold_at) as date, SUM((dresses.selling_price - dresses.buying_price) * sales.quantity) as profit')
            ->where('sales.sold_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('dashboard', compact(
            'total',
            'available',
            'sold',
            'capital',
            'expectedRevenue',
            'profit',
            'dailyProfit',
            'weeklyProfit',
            'monthlyProfit',
            'dailySales',
            'dailyProfitData'
        ));
    }
}
