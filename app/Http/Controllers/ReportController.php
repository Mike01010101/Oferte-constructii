<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Offer;

class ReportController extends Controller
{
    public function index()
    {
        $company = Auth::user()->company;

        // Calculăm statisticile
        $totalOffers = $company->offers()->count();
        
        $totalValueAccepted = $company->offers()
            ->where('status', 'Acceptata')
            ->sum('total_value');

        // Preluăm ultimele 5 oferte
        $latestOffers = $company->offers()
            ->with('client')
            ->latest()
            ->take(5)
            ->get();

        return view('reports.index', compact(
            'totalOffers',
            'totalValueAccepted',
            'latestOffers'
        ));
    }
}