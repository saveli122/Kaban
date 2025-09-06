<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class KanbanSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Очистим (опционально). Если не нужно — закомментируй.
            Task::query()->delete();
            Column::query()->delete();
            Board::query()->delete();

            $now = Carbon::now();

            // 1) Доска
            $board = Board::create([
                'name' => 'Demo Board',
            ]);

            // 2) Колонки с фиксированными позициями
            $columnsData = [
                ['name' => 'Backlog'],
                ['name' => 'In Progress'],
                ['name' => 'Review'],
                ['name' => 'Done'],
            ];

            $columns = [];
            foreach ($columnsData as $idx => $data) {
                $columns[$idx] = Column::create([
                    'board_id' => $board->id,
                    'name'     => $data['name'],
                    'position' => $idx + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // 3) Задачи (слегка случайные, с корректной нумерацией position)
            $faker = \Faker\Factory::create('ru_RU');

            $makeTasks = function (Column $col, int $min, int $max) use ($faker, $now) {
                $count = random_int($min, $max);
                for ($i = 1; $i <= $count; $i++) {
                    // создаём реалистичные даты
                    $created = $now->copy()->subDays(random_int(3, 30))->subMinutes(random_int(0, 1440));
                    $updated = $created->copy()->addDays(random_int(0, 5))->addMinutes(random_int(0, 600));

                    Task::create([
                        'column_id'   => $col->id,
                        'title'       => Str::limit($faker->sentence(random_int(2, 6)), 80),
                        'description' => $faker->optional(0.6)->paragraphs(random_int(1, 2), true),
                        'position'    => $i, // ВАЖНО: последовательная нумерация с 1
                        'created_at'  => $created,
                        'updated_at'  => $updated,
                    ]);
                }
            };

            // Больше задач в Backlog / In Progress; меньше в Done
            $makeTasks($columns[0], 4, 7);
            $makeTasks($columns[1], 3, 6);
            $makeTasks($columns[2], 2, 4);
            $makeTasks($columns[3], 2, 3);
        });
    }
}
