<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['addresses', 'orders'])->get();
    return response()->json($users);
    }


    // public function userAddresses()
    // {
    //     $usersWithAddresses = User::with('userAddresses.address')->get();

    //     return response()->json($usersWithAddresses);
    // }


    public function userAddresses()
    {
        // Fetch users with their addresses
        $usersWithAddresses = User::with('addresses')->get();

        return response()->json($usersWithAddresses);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
