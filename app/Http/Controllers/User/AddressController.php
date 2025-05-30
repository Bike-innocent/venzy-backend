<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{


    public function index()
    {
        $addresses = Address::where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($addresses);
    }


    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'dial_code' => 'nullable|string|max:10',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'is_default' => 'nullable|boolean',
        ]);

        $user = Auth::user();

        $hasAddresses = Address::where('user_id', $user->id)->exists();

        $setAsDefault = false;

        // If user checked is_default OR they have no existing address, set this as default
        if ($request->boolean('is_default') || !$hasAddresses) {
            // Unset other default addresses if needed
            Address::where('user_id', $user->id)->update(['is_default' => false]);
            $setAsDefault = true;
        }

        $address = Address::create([
            'user_id' => $user->id,
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'dial_code' => $request->dial_code,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'is_default' => $setAsDefault,
        ]);

        return response()->json([
            'message' => 'Address saved successfully.',
            'data' => $address
        ], 201);
    }


    public function setDefault(Address $address)
    {
        $user = Auth::user();

        if ($address->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Unset previous default
        Address::where('user_id', $user->id)->update(['is_default' => false]);

        // Set this one as default
        $address->update(['is_default' => true]);

        // Get updated list of addresses with default first
        $addresses = Address::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'message' => 'Default address updated.',
            'data' => $addresses,
        ]);
    }
}