<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            // Ссылка на родителя. null = корневой элемент.
            // constrained() создает связь, а nullOnDelete() означает: если удалить родителя, дети станут корневыми.
            $table->foreignId('parent_id')->nullable()->constrained('bom_items')->nullOnDelete();
            $table->string('name');
            $table->string('unit', 10)->default('шт');
            $table->decimal('quantity', 12, 3)->default(1);
            $table->integer('sort_order')->default(0); // порядок элементов
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_items');
    }
};
