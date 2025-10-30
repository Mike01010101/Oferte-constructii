<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $company = Auth::user()->company;

        // ---- GESTIONARE FILTRU DE DATĂ ----
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
        $carbonStartDate = Carbon::parse($startDate)->startOfDay();
        $carbonEndDate = Carbon::parse($endDate)->endOfDay();

        // ---- QUERY DE BAZĂ ----
        $offersQuery = $company->offers()->whereBetween('offer_date', [$carbonStartDate, $carbonEndDate]);

        // ---- CALCULE PENTRU CARDURI (KPIs) - ACTUALIZATE ----
        $totalOffers = $offersQuery->count();
        $totalValue = $offersQuery->sum('total_value');
        $invoicedUnpaidValue = (clone $offersQuery)->where('status', 'Facturata')->sum('total_value');
        $totalInvoicedValue = (clone $offersQuery)->whereIn('status', ['Facturata', 'Incasata'])->sum('total_value');
        $cashedInValue = (clone $offersQuery)->where('status', 'Incasata')->sum('total_value'); // NOU

        // ---- DATE PENTRU GRAFICE ----
        $statusDistribution = (clone $offersQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $monthlyValues = (clone $offersQuery)
            ->select(DB::raw('SUM(total_value) as total'), DB::raw("DATE_FORMAT(offer_date, '%Y-%m') as month"))
            ->groupBy('month')->pluck('total', 'month')->all();
        
        $period = CarbonPeriod::create($carbonStartDate, '1 month', $carbonEndDate);
        $monthlyChartData = [];
        foreach ($period as $date) {
            $monthKey = $date->format('Y-m');
            $monthlyChartData[$date->format('M Y')] = $monthlyValues[$monthKey] ?? 0;
        }

        $topClients = (clone $offersQuery)
            ->join('clients', 'offers.client_id', '=', 'clients.id')
            ->select('clients.name', DB::raw('SUM(offers.total_value) as total_value'))
            ->groupBy('clients.name')->orderBy('total_value', 'desc')->take(5)
            ->pluck('total_value', 'name')->all();

        return view('reports.index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalOffers' => $totalOffers,
            'totalValue' => $totalValue,
            'invoicedUnpaidValue' => $invoicedUnpaidValue,
            'totalInvoicedValue' => $totalInvoicedValue,
            'cashedInValue' => $cashedInValue,
            'statusDistribution' => $statusDistribution,
            'monthlyChartData' => $monthlyChartData,
            'topClients' => $topClients,
        ]);
    }
}