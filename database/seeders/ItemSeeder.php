<?php

namespace Database\Seeders;

use App\Models\Item; // ✅ Важно: импортируем модель Item
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ✅ Внутри run() мы вставляем наш код
        $items = [
            ['name' => 'Болт М10х45', 'unit' => 'шт'],
            ['name' => 'Гайка М10', 'unit' => 'шт'],
            ['name' => 'Подшипник 6204', 'unit' => 'шт'],
            ['name' => 'Масло И-20', 'unit' => 'л'],
            ['name' => 'Провод ПВ3 2.5', 'unit' => 'м'],
            ['name' => 'Сталь 45 (лист 2мм)', 'unit' => 'кг'],
            ['name' => 'Краска защитная серая', 'unit' => 'л'],
            ['name' => 'Вал ведущий', 'unit' => 'шт'],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
