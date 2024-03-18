<?php

namespace App\Http\Controllers;

class ExampleController extends Controller
{
    public function example()
    {
        return response()->json([
            'message' => 'Example controller',
        ]);
    }
}