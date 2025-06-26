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
                $q->orderByDesc('is_default')->orderByDesc('id')->limit(1);
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



  public function show($id)
{
    $user = \App\Models\User::with([
        'addresses' => function ($q) {
            $q->orderByDesc('is_default')->limit(1); // Default first, fallback to any
        },
        'orders'
    ])
    ->withCount('orders')
    ->withSum('orders', 'total_amount')
    ->findOrFail($id);

    $address = $user->addresses->first(); // This will be default if exists, else first available

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'dial_code' => $user->dial_code,
        'created_at' => $user->created_at,
        'orders_count' => $user->orders_count,
        'total_spent' => $user->orders_sum_total_amount,
        'address' => $address ? [
            'full_name' => $address->full_name,
            'phone' => $address->phone,
            'dial_code' => $address->dial_code,
            'address_line_1' => $address->address_line_1,
            'address_line_2' => $address->address_line_2,
            'city' => $address->city,
            'state' => $address->state,
            'country' => $address->country,
        ] : null,
        'orders' => $user->orders->map(function ($order) {
            return [
                'id' => $order->id,
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'order_date' => $order->order_date,
            ];
        }),
    ]);
}

}