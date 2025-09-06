<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function index()
    {
        return Board::latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        return Board::create($data);
    }

    public function show(Board $board)
    {
        return $board;
    }

    public function update(Request $request, Board $board)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        $board->update($data);
        return $board->fresh();
    }

    public function destroy(Board $board)
    {
        $board->delete();
        return response()->noContent();
    }
}
