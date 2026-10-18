<!-- @refresh reload -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import NestedComponent from './NestedComponent.vue'; // Импортируем наш рекурсивный список

// Древовидная структура данных вашей MES (Дерево спецификации / BOM)
// Каждый элемент имеет свойство 'unit' для корректной работы выпадающего списка
const bomTree = ref([
    {
        id: 1,
        name: 'Корпус редуктора в сборе',
        unit: 'шт',
        quantity: 1,
        children: [
            { id: 11, name: 'Литой корпус (основание)', unit: 'шт', quantity: 1, children: [] },
            { id: 12, name: 'Крышка верхняя', unit: 'шт', quantity: 1, children: [] },
            { id: 13, name: 'Краска защитная (серая)', unit: 'л', quantity: 0.35, children: [] }
        ]
    },
    {
        id: 2,
        name: 'Вал ведущий в сборе',
        unit: 'шт',
        quantity: 1,
        children: [
            { id: 21, name: 'Вал шестерня главная', unit: 'шт', quantity: 1, children: [] },
            {
                id: 22,
                name: 'Подшипниковый узел',
                unit: 'шт',
                quantity: 2,
                children: [
                    { id: 221, name: 'Ролик подшипника', unit: 'шт', quantity: 14, children: [] },
                    { id: 222, name: 'Сепаратор латунный', unit: 'шт', quantity: 1, children: [] },
                    { id: 223, name: 'Смазка технологическая Литол-24', unit: 'г', quantity: 45, children: [] }
                ]
            }
        ]
    },
    {
        id: 3,
        name: 'Электропроводка и крепеж',
        unit: 'шт',
        quantity: 1,
        children: [
            { id: 31, name: 'Болт крепежный М10х45', unit: 'шт', quantity: 12, children: [] },
            { id: 32, name: 'Провод медный заземления ПВАМ', unit: 'м', quantity: 1.85, children: [] }
        ]
    }
]);

// Функция добавления элемента на самый верхний (корневой) уровень спецификации
const addRootItem = () => {
    const newId = Date.now();
    bomTree.value.push({
        id: newId,
        name: 'Новая корневая сборка #' + String(newId).slice(-4),
        unit: 'шт',
        quantity: 1,
        children: []
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Конструктор спецификаций MES (Бесконечная вложенность)
            </h2>
        </template>

        <div class="py-12">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-9">

                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">🌲 Дерево состава изделия (BOM)</h5>
                            </div>

                            <div class="card-body bg-white">
                                <p class="text-muted small mb-4">
                                    Вы можете перетаскивать детали вверх/вниз, а также **затаскивать их внутрь других деталей**, создавая подуровни сборки.
                                </p>

                                <!-- Кнопка добавления корневого элемента -->
                                <div class="mb-3 text-end">
                                    <button @click="addRootItem" class="btn btn-sm btn-dark fw-bold shadow-sm">
                                        ➕ Добавить деталь в корень спецификации
                                    </button>
                                </div>

                                <!-- Корневой вызов нашего рекурсивного компонента -->
                                <div class="nested-tree-wrapper">
                                    <NestedComponent :tasks="bomTree" />
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Стили смещения вложенных уровней */
.nested-tree-wrapper > .drag-area {
    margin-left: 0 !important;
}
</style>
