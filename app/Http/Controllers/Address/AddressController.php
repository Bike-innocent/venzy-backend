<?php

namespace App\Http\Controllers\Address;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    // Display a list of addresses
    public function index()
    {
        $usersWithAddresses = User::with('addresses')->get();

        return response()->json($usersWithAddresses);
    }

    // Store a new address



    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
        ]);

        // Check if the user already has any addresses
        $hasAddress = Address::where('user_id', auth()->id())->exists();

        // Set is_default to true if this is the first address
        $validated['is_default'] = !$hasAddress;

        // Create the address
        $address = auth()->user()->addresses()->create($validated);

        return response()->json($address, 201);
    }





    public function authUserAddresses()
    {
        $addresses = Address::where('user_id', auth()->id())->get();
        return response()->json($addresses);
    }




    // Show a single address
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





    public function update(Request $request, Address $address)
    {


        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'is_default' => 'sometimes|boolean', // Allow true or false
        ]);

        $validated['is_default'] = $request->input('is_default') == '1';



        // If this address is set as default, remove default from all others
        if ($validated['is_default']) {
            Address::where('user_id', auth()->id())
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json($address);
    }







    // Delete an address
    public function destroy(Address $address)
    {
        // Check if the address belongs to the authenticated user
        if ($address->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Soft delete the address
        $address->delete();

        // If it was the default address, update the next available address as default
        $remainingAddresses = Address::where('user_id', auth()->id())->get();

        if ($address->is_default && $remainingAddresses->count() > 0) {
            $remainingAddresses->first()->update(['is_default' => true]);
        }

        return response()->json(['message' => 'Address deleted successfully.']);
    }
}