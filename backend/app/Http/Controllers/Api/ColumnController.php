<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Column;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'board_id' => 'required|exists:boards,id',
            'name'     => 'required|string|max:255',
            'position' => 'nullable|integer|min:0',
        ]);
        return Column::create($data);
    }

    public function update(Request $request, Column $column)
    {
        $data = $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'position' => 'sometimes|integer|min:0',
        ]);
        $column->update($data);
        return $column->fresh();
    }

    public function destroy(Column $column)
    {
        $column->delete();
        return response()->noContent();
    }
}
