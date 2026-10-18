<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue'; // ✅ Добавили watch
import NestedComponent from './NestedComponent.vue';

const props = defineProps({
    bomTree: { type: Array, default: () => [] }
});

const tree = ref(props.bomTree);

// Функция сохранения

const saveTree = () => {
    // Рекурсивная функция, которая берет данные из vuedraggable и собирает чистое дерево
    const buildCleanTree = (items) => {
        if (!items || !Array.isArray(items)) return [];
        return items.map(item => ({
            name: item.name,
            unit: item.unit,
            quantity: item.quantity,
            // Рекурсивно вызываем сборку для детей
            children: buildCleanTree(item.children)
        }));
    };

    const cleanTree = buildCleanTree(tree.value);

    // Проверим в консоли, что дети на месте
    console.log('Отправляем на сервер:', cleanTree);

    router.post('/bom/save', { tree: cleanTree }, {
        preserveScroll: true,
    });
};




// Функция добавления корневого элемента
const addRootItem = () => {
    const newId = crypto.randomUUID?.() ?? Date.now() + Math.random();
    tree.value.push({
        id: newId,
        name: 'Новая сборка #' + String(newId).slice(-4),
        unit: 'шт',
        quantity: 1,
        children: []
    });
    // watch сам вызовет saveTree, но можно вызвать и явно для надежности
    saveTree();
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
                                    Вы можете перетаскивать детали вверх/вниз, а также затаскивать их внутрь других деталей, создавая подуровни сборки.
                                </p>

                                <!-- Кнопка добавления корневого элемента -->
                                <div class="mb-3 text-end">
                                    <button @click="addRootItem" class="btn btn-sm btn-dark fw-bold shadow-sm">
                                        ➕ Добавить деталь в корень спецификации
                                    </button>
                                </div>

                                <!-- Корневой вызов нашего рекурсивного компонента -->
                                <div class="nested-tree-wrapper">
                                    <NestedComponent :tasks="tree" @tree-changed="saveTree" />
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
