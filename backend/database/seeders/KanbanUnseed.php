<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;

class KanbanUnseed extends Seeder
{
    public function run(): void
    {
        // Вариант 1: удалить только демо-доску, созданную сидером
        if ($demo = Board::where('name', 'Demo Board')->first()) {
            // удаляем связанные задачи и колонки каскадом
            foreach ($demo->columns as $col) {
                Task::where('column_id', $col->id)->delete();
            }
            Column::where('board_id', $demo->id)->delete();
            $demo->delete();
            return;
        }

        // Вариант 2: полностью очистить три таблицы (DEV-окружение)
        Schema::disableForeignKeyConstraints();
        Task::truncate();
        Column::truncate();
        Board::truncate();
        Schema::enableForeignKeyConstraints();
    }
}
