<?php
namespace App\Http\Controllers\Product;

use App\Models\Colour;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ColourController extends Controller
{
    public function index()
    {
        return Colour::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hex_code' => 'nullable|string|max:7',
        ]);

        $colour = Colour::create($validated);
        return response()->json($colour, 201);
    }

    public function show(Colour $colour)
    {
        return $colour;
    }

    public function update(Request $request, Colour $colour)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hex_code' => 'nullable|string|max:7',
        ]);

        $colour->update($validated);
        return response()->json($colour);
    }

    public function destroy(Colour $colour)
    {
        $colour->delete();
        return response()->json(null, 204);
    }

    public function restore($id)
    {
        $colour = Colour::withTrashed()->find($id);

        if ($colour) {
            $colour->restore();
            return response()->json(['message' => 'Colour restored successfully']);
        } else {
            return response()->json(['message' => 'Colour not found'], 404);
        }
    }
}
