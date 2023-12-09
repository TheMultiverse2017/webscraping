<?php

namespace App\Http\Controllers;

use App\Enum\RoleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(Auth::check()){
            if(Auth::user()->role==RoleType::ADMIN->value){
                return view('ADMIN.home');
            }
        }
        else{
                return view('WEBSITE.home');
        }
    }
}
