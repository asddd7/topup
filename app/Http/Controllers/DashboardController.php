<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Banner;

class DashboardController extends Controller
{

    public function index()
    {

        $banners = Banner::where('is_active',1)
            ->orderBy('sort_order')
            ->get();


        return view('dashboard', compact('banners'));

    }
}