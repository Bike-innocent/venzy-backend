<?php
namespace App\Http\Controllers\Product;

use App\Models\Size;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SizeController extends Controller
{
    public function index()
    {
        return Size::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $size = Size::create($validated);
        return response()->json($size, 201);
    }

    public function show(Size $size)
    {
        return $size;
    }

    public function update(Request $request, Size $size)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $size->update($validated);
        return response()->json($size);
    }

    public function destroy(Size $size)
    {
        $size->delete();
        return response()->json(null, 204);
    }

    public function restore($id)
    {
        $size = Size::withTrashed()->find($id);

        if ($size) {
            $size->restore();
            return response()->json(['message' => 'Size restored successfully']);
        } else {
            return response()->json(['message' => 'Size not found'], 404);
        }
    }
}
