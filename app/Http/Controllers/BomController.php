<?php

namespace App\Http\Controllers;

use App\Models\Item; // В самом верху файла
use App\Models\BomItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BomController extends Controller
{

    public function index()
    {
        $tree = BomItem::whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('sort_order')
            ->get();

        $formattedTree = $this->formatTree($tree);

        // ✅ Достаем справочник и сортируем по имени
        $catalogItems = Item::orderBy('name')->get();

        return Inertia::render('Dashboard', [
            'bomTree' => $formattedTree,
            'catalogItems' => $catalogItems // Передаем во Vue
        ]);
    }

    // Новая вспомогательная функция для форматирования
    private function formatTree($items)
    {
        return $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'unit' => $item->unit,
                'quantity' => $item->quantity,
                // Рекурсивно форматируем детей. Если детей нет - отдаем пустой массив []
                'children' => $this->formatTree($item->childrenRecursive ?? collect([])),
            ];
        })->toArray();
    }

    public function save(Request $request)
    {
        // ✅ ИСПОЛЬЗУЕМ $request->input('tree') ВМЕСТО $validated['tree']
        // Валидация проверяет верхний уровень, но мы берем сырые данные целиком
        $request->validate([
            'tree' => 'present|array',
            'tree.*.name' => 'required|string|max:255',
            'tree.*.unit' => 'required|string|max:10',
            'tree.*.quantity' => 'required|numeric|min:0',
        ]);

        // Берем данные НАПРЯМУЮ из запроса. Laravel не будет их фильтровать.
        $treeData = $request->input('tree');

        DB::transaction(function () use ($treeData) {
            BomItem::query()->delete();
            $this->insertTree($treeData, null);
        });

        return back();
    }



    private function insertTree(array $nodes, ?int $parentId): void
    {
        foreach ($nodes as $index => $node) {
            $item = BomItem::create([
                'parent_id' => $parentId,
                'name' => $node['name'],
                'unit' => $node['unit'],
                'quantity' => $node['quantity'],
                'sort_order' => $index,
            ]);

            // Проверяем, есть ли дети, и рекурсивно вызываем функцию
            if (isset($node['children']) && is_array($node['children']) && count($node['children']) > 0) {
                $this->insertTree($node['children'], $item->id);
            }
        }
    }


}
