<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Warehouse;
use App\Biller;
use App\Account;
use App\PosSetting;
use App\GeneralSetting;
use DB;

class SiatController extends Controller
{
    public function index()
    {
        return view('setting.siat_setting');

    }
}
