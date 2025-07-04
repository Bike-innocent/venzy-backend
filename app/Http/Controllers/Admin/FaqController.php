<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    // Admin: List all FAQs
    public function index()
    {
        return response()->json(Faq::latest()->get());
    }

    // Admin: Store new FAQ
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $faq = Faq::create($validated);

        return response()->json(['message' => 'FAQ created successfully', 'faq' => $faq]);
    }

    //show single FAQ
    public function show(Faq $faq)
    {
        return response()->json($faq);
    }

    // Admin: Update FAQ
    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'question' => 'sometimes|required|string|max:255',
            'answer' => 'sometimes|required|string',
            'is_active' => 'boolean',
        ]);

        $faq->update($validated);

        return response()->json(['message' => 'FAQ updated successfully', 'faq' => $faq]);
    }

    // Admin: Delete FAQ
    public function destroy(Faq $faq)
    {
        $faq->delete();

        return response()->json(['message' => 'FAQ deleted successfully']);
    }

    // Admin: Toggle Active Status
    public function toggle(Faq $faq)
    {
        $faq->is_active = !$faq->is_active;
        $faq->save();

        return response()->json(['message' => 'FAQ status updated', 'is_active' => $faq->is_active]);
    }

    // Public: Only Active FAQs (for frontend display)
    public function publicIndex()
    {
        return response()->json(Faq::where('is_active', true)->orderBy('id', 'desc')->get());
    }


    public function hasAny()
    {
        $exists = Faq::where('is_active', true)->exists();
        return response()->json(['hasFaq' => $exists]);
    }
}