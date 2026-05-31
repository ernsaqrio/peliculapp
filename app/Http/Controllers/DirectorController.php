<?php

namespace App\Http\Controllers;

use App\Models\Director;
use Illuminate\Http\Request;

class DirectorController extends Controller
{
    public function index()
    {
        return response()->json(Director::all(), 200);
    }

    public function show(Director $director)
    {
        return response()->json($director, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'surname' => 'required|string',
            'birthdate' => 'required|date',
        ]);

        $director = Director::create($validated);

        return response()->json($director, 201);
    }

    public function update(Request $request, Director $director)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'surname' => 'sometimes|string',
            'birthdate' => 'sometimes|date',
        ]);

        $director->update($validated);

        return response()->json($director, 200);
    }

    public function destroy(Director $director)
    {
        $director->delete();

        return response()->json([
            'message' => 'Director deleted',
        ], 200);
    }
}
