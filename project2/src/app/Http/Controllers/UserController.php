<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Analytics;

class UserController extends Controller
{
    public function index() {
      $x = Analytics::get();
      dd($x);
		$response = Http::get("http://nginx_one/users");
     
    	return $response->json();
    }
}
