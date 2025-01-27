<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserDetailsService;

class UserDetailsController extends Controller
{
    // Responsable de obtener datos específicos del usuario y de sus relaciones.
    protected $userDetailsService;

    public function __construct(UserDetailsService $userDetailsService)
    {
        $this->userDetailsService = $userDetailsService;
    }

    public function getUserDetails(Request $request)
    {
        $userId = $request->user()->id;
        $userDetails = $this->userDetailsService->getUserDetails($userId);

        return response()->json($userDetails);
    }

    public function updateUserDetails(Request $request)
    {
        $userId = $request->user()->id;
        $data = $request->all();
        $updatedUserDetails = $this->userDetailsService->updateUserDetails($userId, $data);

        return response()->json($updatedUserDetails);
    }
}