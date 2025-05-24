<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;

// use App\Models\User;

use Illuminate\Support\Facades\Auth;



class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $user->load('roles', 'permissions');


        return response()->json($user);
    }


}
