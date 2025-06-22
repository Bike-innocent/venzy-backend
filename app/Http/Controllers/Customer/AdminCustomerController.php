<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AdminCustomerController extends Controller
{
    //  public function index()
    // {
    //     $customers = User::withCount('orders')
    //         ->withSum('orders', 'total_amount')
    //         ->with(['addresses' => function ($query) {
    //             $query->latest()->limit(1); // Get latest address for location
    //         }])
    //         ->whereHas('orders') // Only customers who have placed at least one order
    //         ->orderByDesc('orders_count')
    //         ->get()
    //         ->map(function ($user) {
    //             return [
    //                 'id' => $user->id,
    //                 'name' => $user->name,
    //                 'email' => $user->email,
    //                 'orders_count' => $user->orders_count,
    //                 'total_spent' => $user->orders_sum_total_amount,
    //                 'location' => optional($user->addresses->first(), fn ($addr) => "{$addr->city}, {$addr->state}") ?? 'N/A',
    //             ];
    //         });

    //     return response()->json($customers);
    // }





public function index()
{
    $customers = \App\Models\User::whereHas('orders')
        ->withCount('orders')
        ->withSum('orders', 'total_amount')
        ->with(['addresses' => function ($q) {
            $q->where('is_default', true)->limit(1);
        }])
        ->orderByDesc('orders_count')
        ->get()
        ->map(function ($user) {
            $address = $user->addresses->first();
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'orders_count' => $user->orders_count,
                'total_spent' => $user->orders_sum_total_amount,
                'location' => $address ? "{$address->city}, {$address->state}" : 'N/A',
            ];
        });

    return response()->json($customers);
}
}