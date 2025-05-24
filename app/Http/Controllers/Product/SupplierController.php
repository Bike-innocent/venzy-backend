<?php

// namespace App\Http\Controllers\Product;

// use App\Models\Supplier;
// use Illuminate\Http\Request;
// use App\Http\Controllers\Controller;

// class SupplierController extends Controller
// {
//     public function index()
//     {
//         return Supplier::all();
//     }

//     public function store(Request $request)
//     {
//         $validated = $request->validate([
//             'name' => 'required|string|max:255',
//             'contact_name' => 'required|string|max:255',
//             'email' => 'required|email|unique:suppliers,email',
//             'phone' => 'required|string|max:15',
//             'address_id' => 'required|exists:addresses,id'
//         ]);

//         $supplier = Supplier::create($validated);
//         return response()->json($supplier, 201);
//     }

//     public function show(Supplier $supplier)
//     {
//         return $supplier;
//     }

//     public function update(Request $request, Supplier $supplier)
//     {
//         $validated = $request->validate([
//             'name' => 'required|string|max:255',
//             'contact_name' => 'required|string|max:255',
//             'email' => 'required|email|unique:suppliers,email,' . $supplier->id,
//             'phone' => 'required|string|max:15',
//             'address_id' => 'required|exists:addresses,id'
//         ]);

//         $supplier->update($validated);
//         return response()->json($supplier);
//     }

//     public function destroy(Supplier $supplier)
//     {
//         $supplier->delete();
//         return response()->json(null, 204);
//     }

//     public function restore($id)
//     {
//         $supplier = Supplier::withTrashed()->find($id);

//         if ($supplier) {
//             $supplier->restore();
//             return response()->json(['message' => 'Supplier restored successfully']);
//         } else {
//             return response()->json(['message' => 'Supplier not found'], 404);
//         }
//     }
// }





namespace App\Http\Controllers\Product;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;

class SupplierController extends Controller
{
    /**
     * Fetch all users with the "supplier" role.
     */
    public function index()
    {
        // Get all users who have the "supplier" role
        $suppliers = User::role('supplier')->get();

        return response()->json($suppliers);
    }

    /**
     * Store a new supplier by creating a user and assigning the "supplier" role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:15',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create the user
        $supplier = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => bcrypt($validated['password']),
        ]);

        // Assign the "supplier" role to the user
        $supplier->assignRole('supplier');

        return response()->json($supplier, 201);
    }

    /**
     * Show a specific supplier by user ID.
     */
    public function show($id)
    {
        $supplier = User::role('supplier')->find($id);

        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }

        return response()->json($supplier);
    }

    /**
     * Update supplier information.
     */
    public function update(Request $request, $id)
    {
        $supplier = User::role('supplier')->find($id);

        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $supplier->id,
            'phone' => 'sometimes|required|string|max:15',
            'password' => 'sometimes|nullable|string|min:8|confirmed',
        ]);

        // Update supplier details
        $supplier->update([
            'name' => $validated['name'] ?? $supplier->name,
            'email' => $validated['email'] ?? $supplier->email,
            'phone' => $validated['phone'] ?? $supplier->phone,
            'password' => isset($validated['password']) ? bcrypt($validated['password']) : $supplier->password,
        ]);

        return response()->json($supplier);
    }

    /**
     * Delete a supplier (soft delete).
     */
    public function destroy($id)
    {
        $supplier = User::role('supplier')->find($id);

        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }

        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully'], 204);
    }

    /**
     * Restore a soft-deleted supplier.
     */
    public function restore($id)
    {
        $supplier = User::onlyTrashed()->role('supplier')->find($id);

        if ($supplier) {
            $supplier->restore();
            return response()->json(['message' => 'Supplier restored successfully']);
        } else {
            return response()->json(['message' => 'Supplier not found'], 404);
        }
    }
}
