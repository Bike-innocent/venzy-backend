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

        $addressCount = Address::where('user_id', $user->id)->count();
        if ($addressCount >= 5) {
            return response()->json([
                'message' => 'You can only have up to 5 addresses. Please delete or update an existing one.'
            ], 422); // Unprocessable Entity
        }

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






    public function show($id)
    {

        $address = Address::find($id);

        if ($address->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }


        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        return response()->json($address, 200);
    }





    public function update(Request $request, $id)
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

        $address = Address::find($id);

        if (!$address || $address->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized or address not found'], 403);
        }

        // If this address should be default, unset all others first
        if ($request->boolean('is_default')) {
            Address::where('user_id', auth()->id())->update(['is_default' => false]);
        }

        $address->update([
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'dial_code' => $request->dial_code,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'is_default' => $request->boolean('is_default'),
        ]);

        return response()->json([
            'message' => 'Address updated successfully.',
            'data' => $address,
        ]);
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


    public function destroy($id)
    {
        $address = Address::find($id);

        if (!$address || $address->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized or address not found'], 403);
        }

        $wasDefault = $address->is_default;

        // Delete the address
        $address->delete();

        // If it was default, assign another as default if exists
        if ($wasDefault) {
            $nextAddress = Address::where('user_id', auth()->id())
                ->orderByDesc('created_at')
                ->first();

            if ($nextAddress) {
                $nextAddress->update(['is_default' => true]);
            }
        }

        $user = Auth::user();

        $addresses = Address::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'message' => 'Address deleted successfully.',
            'data' => $addresses,
        ]);
    }
}