<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
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
