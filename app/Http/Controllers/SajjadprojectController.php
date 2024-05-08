<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;

class SajjadprojectController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;



    public function sajjadhome ($language)
    {
     return view('sajjadproject.home1');
    }


    public function weblog ($language)
    {
     return view('sajjadproject.weblog');
    }

public function webdesign ($language)
    {
     return view('sajjadproject.webdesign');
    }

public function application ($language)
    {
     return view('sajjadproject.application');
    }

public function protfolio ($language)
    {
     return view('sajjadproject.protfolio');
    }

public function services ($language)
    {
     return view('sajjadproject.services');
    }

public function order ($language)
    {
     return view('sajjadproject.order');
    }

public function termsand ($language)
    {
     return view('sajjadproject.termsand');
    }

public function about ($language)
    {
     return view('sajjadproject.about');
    }

public function contactus ($language)
    {
     return view('sajjadproject.contactus');
    }


    }







