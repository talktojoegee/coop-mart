<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function __construct(){
        $this->vendor = new Vendor();
    }


    public function showShops(){
        return 'there!';
    }
}
