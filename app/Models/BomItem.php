<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BomItem extends Model
{
    // Разрешаем сохранение этих полей
    protected $fillable = ['parent_id', 'name', 'unit', 'quantity', 'sort_order'];

    // Обычная связь
    public function children(): HasMany
    {
        return $this->hasMany(BomItem::class, 'parent_id')->orderBy('sort_order');
    }

    //   Рекурсивная загрузка всех потомков
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    // Связь "у элемента есть родитель"
    public function parent(): BelongsTo
    {
        return $this->belongsTo(BomItem::class, 'parent_id');
    }
}
