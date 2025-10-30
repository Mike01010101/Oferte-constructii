<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $company = $user->company;

        // Ofertele alocate utilizatorului curent care nu sunt finalizate
        $myOpenOffers = $company->offers()
            ->where('assigned_to_user_id', $user->id)
            ->whereNotIn('status', ['Finalizata', 'Incasata', 'Anulata', 'Respinsa'])
            ->with('client')
            ->latest()
            ->get();

        // Sumar oferte pe statusuri pentru întreaga companie
        $offerStatusSummary = $company->offers()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('home', compact('myOpenOffers', 'offerStatusSummary'));
    }
}
