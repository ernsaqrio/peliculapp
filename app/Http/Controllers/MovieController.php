<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(
            Movie::all(),
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'release_date' => 'required|date',
            'sinopsis' => 'required|string',
            'duration' => 'required|integer',
            'gendre' => 'required|string|max:255',
            'director_id' => 'required|exists:directors,id',
        ]);

        $movie = Movie::create($validated);

        return response()->json($movie, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $movie = Movie::findOrFail($id);

        return response()->json($movie, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $movie = Movie::findOrFail($id);

        $movie->update($request->all());

        return response()->json($movie, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $movie = Movie::findOrFail($id);

        $movie->delete();

        return response()->json([
            'message' => 'Movie deleted successfully',
        ], 200);
    }
}
