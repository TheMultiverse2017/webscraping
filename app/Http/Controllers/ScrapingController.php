<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScrapingController extends Controller
{
    function index(){
        return view(('ADMIN.SCRAPE.index'));
    }
}
