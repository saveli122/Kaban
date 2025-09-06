<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'column_id'   => 'required|exists:columns,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'position'    => 'nullable|integer|min:0',
        ]);
        return Task::create($data);
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'column_id'   => 'sometimes|exists:columns,id',
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'position'    => 'sometimes|integer|min:0',
        ]);
        $task->update($data);
        return $task->fresh();
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->noContent();
    }
}
