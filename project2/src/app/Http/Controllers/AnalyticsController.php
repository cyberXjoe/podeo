<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Analytics;

class AnalyticsController extends Controller
{
    public function callAnalytics($id){
        $saveAnalytics = new Analytics();
        $saveAnalytics->episode_id = $id;
        $saveAnalytics->save();

        return $saveAnalytics;
    }
}
