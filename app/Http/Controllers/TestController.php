<?php

namespace App\Http\Controllers;

use App\Aspects\Logger;
use Illuminate\Http\Request;

class TestController extends Controller
{
    #[Logger]
    public function index(Request $request)
    {
        var_dump('hello from index method');
    }
}
