<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    public function index() {
    	
        $users = [
    		[
    			"id" => 1,
    			"name" => "Edo"
    		],
    		[
    			"id" => 2,
    			"name" => "Dedo"
    		],
    	];
    	return \Response::json($users,200);
    }
}
