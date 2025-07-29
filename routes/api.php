<?php
use Illuminate\Http\Request;
use App\Models\Dress;

Route::post('/sync', function (Request $request) {
    $data = $request->all();

    // Example: create dress offline
    if ($data['type'] === 'create_dress') {
    $dress = Dress::create($data['payload']);
    return response()->json(['status' => 'created', 'dress_id' => $dress->id]);
    }

    // Example: mark as sold
    if ($data['type'] === 'mark_sold') {
        $dress = Dress::find($data['payload']['id']);
        if ($dress) {
            $dress->is_sold = true;
            $dress->save();
        }
    }

    return response()->json(['status' => 'synced']);
});

Route::post('/sync-photo', function (Request $request) {
    $request->validate([
        'photo' => 'required|image|max:2048',
        'dress_id' => 'required|integer'
    ]);

    $photo = $request->file('photo');
    $path = $photo->store('dresses', 'public');

    $dress = Dress::find($request->dress_id);
    if ($dress) {
        $dress->photo = $path;
        $dress->save();
    }

    return response()->json(['status' => 'photo uploaded']);
});
