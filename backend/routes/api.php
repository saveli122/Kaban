<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\ColumnController;
use App\Http\Controllers\Api\TaskController;

Route::apiResource('boards', BoardController::class);
Route::apiResource('columns', ColumnController::class)->only(['store','update','destroy']);
Route::apiResource('tasks', TaskController::class)->only(['store','update','destroy']);

// Получить доску с колонками и задачами
Route::get('boards/{board}/full', function (\App\Models\Board $board) {
    return $board->load('columns.tasks');
})->name('boards.full');

// Перемещение задачи (смена колонки/позиции)
Route::post('tasks/{task}/move', function (\Illuminate\Http\Request $r, \App\Models\Task $task) {
    $task->update([
        'column_id' => $r->integer('column_id', $task->column_id),
        'position'  => $r->integer('position', 0),
    ]);
    return $task->fresh('column');
})->name('tasks.move');
