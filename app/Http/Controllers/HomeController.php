<?php 

namespace App\Http\Controllers;

class HomeController extends Controller

{
    public function index()
    {
        if (auth()->check()) {
            return redirect()->route(auth()->user()->rutaInicio());
        }

        return view('home');
    }
}