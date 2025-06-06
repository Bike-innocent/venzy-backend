<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // $user->load('roles', 'permissions');


        // return response()->json($user);

        $user->load('roles', 'permissions','addresses');

        return response()->json(['user' => $user], 201);
    }



    public function updateName(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->name = $request->name;
        $user->save();
        $user->load('roles', 'permissions');

        return response()->json([
            'message' => 'Name updated successfully',
            'user' => $user
        ], 200);
    }


    public function updatePhone(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20',
            'dial_code' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->phone = $request->phone;
        $user->dial_code = $request->dial_code;
        $user->save();
        $user->load('roles', 'permissions');

        return response()->json([
            'message' => 'Phone number updated successfully',
            'user' => $user
        ], 200);
    }
}