<?php

namespace App\Http\Controllers;

use App\Models\Dress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Sale;

class DressController extends Controller
{
    public function index()
    {
        $dresses = Dress::latest()->get();
        return view('dresses.index', compact('dresses'));
    }

    public function create()
    {
        return view('dresses.create');
    }

    public function store(Request $request)
    {
    

        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'buying_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'photo' => 'nullable|image|max:2048',
        ]);

       
        $validated['original_quantity'] = $validated['quantity'];

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('dresses', 'public');
        }

        Dress::create($validated);

        return redirect()->route('dresses.index')->with('success', 'Dress added!');
    }


    public function markAsSold(Request $request, Dress $dress)
    {
        $request->validate([
            'quantity_sold' => 'required|integer|min:1|max:' . $dress->quantity,
        ]);

        $quantitySold = $request->input('quantity_sold');

        // Reduce available stock
        $dress->quantity -= $quantitySold;
        $dress->save();

        // Create sale record
        Sale::create([
            'dress_id' => $dress->id,
            'quantity' => $quantitySold,
            'sold_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Marked as sold.');
    }


    public function destroy(Dress $dress)
    {
        if ($dress->photo) {
            Storage::disk('public')->delete($dress->photo);
        }
        $dress->delete();
        return redirect()->route('dresses.index')->with('success', 'Dress deleted.');
    }
}
