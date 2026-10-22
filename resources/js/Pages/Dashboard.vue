<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue'; // ✅ Добавили computed
import NestedComponent from './NestedComponent.vue';

const props = defineProps({
    bomTree: { type: Array, default: () => [] },
    catalogItems: { type: Array, default: () => [] } // ✅ Принимаем справочник
});

console.log('Данные справочника пришли:', props.catalogItems);

const tree = ref(props.bomTree);

// --- Логика справочника ---
const isCatalogOpen = ref(false);
const catalogSearch = ref('');

// Фильтр для поиска
const filteredCatalog = computed(() => {
    if (!catalogSearch.value) return props.catalogItems;
    return props.catalogItems.filter(item =>
        item.name.toLowerCase().includes(catalogSearch.value.toLowerCase())
    );
});

// Добавление выбранной детали из справочника в КОРЕНЬ
const addFromCatalog = (item) => {
    const newId = crypto.randomUUID?.() ?? Date.now() + Math.random();
    tree.value.push({
        id: newId,
        name: item.name,
        unit: item.unit,
        quantity: 1,
        children: []
    });
    isCatalogOpen.value = false; // Закрываем окно
    catalogSearch.value = '';    // Очищаем поиск
    saveTree();                  // Сохраняем
};
// -------------------------

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

    router.post('/bom/save', { tree: cleanTree }, {
        preserveScroll: true,
    });
};

// Функция добавления пустого корневого элемента
const addRootItem = () => {
    const newId = crypto.randomUUID?.() ?? Date.now() + Math.random();
    tree.value.push({
        id: newId,
        name: 'Новая сборка #' + String(newId).slice(-4),
        unit: 'шт',
        quantity: 1,
        children: []
    });
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

                                <!-- Кнопки добавления -->
                                <div class="mb-3 text-end">
                                    <button @click="isCatalogOpen = true" class="btn btn-sm btn-success fw-bold shadow-sm me-2">
                                        📦 Добавить из справочника
                                    </button>
                                    <button @click="addRootItem" class="btn btn-sm btn-dark fw-bold shadow-sm">
                                        ➕ Добавить пустую деталь
                                    </button>
                                </div>

                                <!-- Корневой вызов рекурсивного компонента -->
                                <div class="nested-tree-wrapper">
                                    <NestedComponent :tasks="tree" @tree-changed="saveTree" />
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно справочника -->
        <div v-if="isCatalogOpen" class="modal d-block" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title">📦 Выбор из справочника</h5>
                        <button @click="isCatalogOpen = false" class="btn-close btn-close-white"></button>
                    </div>
                    <div class="modal-body">
                        <input
                            v-model="catalogSearch"
                            type="text"
                            class="form-control mb-3"
                            placeholder="Поиск детали..."
                        >
                        <div class="list-group" style="max-height: 400px; overflow-y: auto;">
                            <button
                                v-for="item in filteredCatalog"
                                :key="item.id"
                                @click="addFromCatalog(item)"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                            >
                                <span>{{ item.name }}</span>
                                <span class="badge bg-secondary">{{ item.unit }}</span>
                            </button>
                            <div v-if="filteredCatalog.length === 0" class="text-muted text-center p-3">
                                Ничего не найдено
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
