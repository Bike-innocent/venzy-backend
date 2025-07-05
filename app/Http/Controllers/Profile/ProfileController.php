<?php

// namespace App\Http\Controllers\Profile;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// // use App\Models\User;

// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Validator;

// class ProfileController extends Controller
// {
//     public function index()
//     {
//         $user = Auth::user();
//         if (!$user) {
//             return response()->json(['message' => 'User not authenticated'], 401);
//         }

        

//         $user->load('roles', 'permissions','addresses');

//         return response()->json(['user' => $user], 201);
//     }



//     public function updateName(Request $request)
//     {
//         $user = Auth::user();

//         if (!$user) {
//             return response()->json(['message' => 'User not authenticated'], 401);
//         }

//         $validator = Validator::make($request->all(), [
//             'name' => 'required|string|max:255',
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['errors' => $validator->errors()], 422);
//         }

//         $user->name = $request->name;
//         $user->save();
//         $user->load('roles', 'permissions');

//         return response()->json([
//             'message' => 'Name updated successfully',
//             'user' => $user
//         ], 200);
//     }


//     public function updatePhone(Request $request)
//     {
//         $user = Auth::user();

//         if (!$user) {
//             return response()->json(['message' => 'User not authenticated'], 401);
//         }

//         $validator = Validator::make($request->all(), [
//             'phone' => 'required|string|max:20',
//             'dial_code' => 'required|string|max:10',
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['errors' => $validator->errors()], 422);
//         }

//         $user->phone = $request->phone;
//         $user->dial_code = $request->dial_code;
//         $user->save();
//         $user->load('roles', 'permissions');

//         return response()->json([
//             'message' => 'Phone number updated successfully',
//             'user' => $user
//         ], 200);
//     }
// }
















namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $user->load('roles', 'permissions', 'addresses');
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $roles = $user->roles->pluck('name')->toArray();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'dial_code' => $user->dial_code,
                'roles' => $roles,
                'permissions' => $permissions,
                'addresses' => $user->addresses, // Eager loaded above
            ],
        ], 200);
    }

    public function updateName(Request $request)
    {
        $user = $request->user();

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

        return $this->returnUserResponse($user, 'Name updated successfully');
    }

    public function updatePhone(Request $request)
    {
        $user = $request->user();

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

        return $this->returnUserResponse($user, 'Phone number updated successfully');
    }

    protected function returnUserResponse($user, $message = null)
    {
        $user->load('roles', 'permissions', 'addresses');
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $roles = $user->roles->pluck('name')->toArray();

        return response()->json([
            'message' => $message,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'dial_code' => $user->dial_code,
                'roles' => $roles,
                'permissions' => $permissions,
                'addresses' => $user->addresses,
            ],
        ], 200);
    }
}