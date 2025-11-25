<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function companyProfile()
    {
        return view('about.company-profile');
    }

    public function about()
    {
        return view('about.about');
    }
}


