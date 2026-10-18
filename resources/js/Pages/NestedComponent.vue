<template>
    <draggable
        :list="tasks"
        @end="emit('tree-changed')"
        class="drag-area list-group"
        group="bom-tree"
        item-key="id"
        handle=".handle"
        :animation="200"
        ghost-class="sortable-ghost"
        :empty-insert-threshold="40"
    >
        <template #item="{ element, index }">
            <li class="list-group-item border-0 ps-0 pb-1 tree-node">

                <!-- Плашка элемента -->
                <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded border shadow-xs mb-1">
                    <div class="d-flex align-items-center flex-grow-1 min-w-0">
                        <button
                            v-if="element.children && element.children.length > 0"
                            @click="toggleExpand(element.id)"
                            class="btn btn-sm btn-link text-decoration-none p-0 me-2 text-dark fw-bold fs-5"
                        >
                            {{ isExpanded(element.id) ? '▼' : '▶' }}
                        </button>
                        <span v-else class="me-4"></span>

                        <span class="handle me-2 text-secondary fs-5" style="cursor: grab;">☰</span>
                        <strong class="fs-6 text-truncate" :title="element.name">{{ element.name }}</strong>
                    </div>

                    <div class="action-column d-flex align-items-center justify-content-end ms-3 gap-2">
                        <input
                            type="number"
                            v-model.number="element.quantity"
                            class="form-control form-control-sm text-center fw-bold px-1 rounded-start"
                            min="0" step="any" style="max-width: 75px;"
                        >
                        <select
                            v-model="element.unit"
                            class="form-select form-select-sm font-monospace border-start-0 bg-white"
                            style="width: 75px; font-size: 0.8rem;"
                            @change="onUnitChange(element)"
                        >
                            <option value="шт">шт</option>
                            <option value="кг">кг</option>
                            <option value="г">г</option>
                            <option value="л">л</option>
                            <option value="м">м</option>
                        </select>
                        <button @click="addChildItem(element)" class="btn btn-sm btn-outline-success px-2" title="Добавить поддеталь внутрь">+</button>
                        <button @click="removeItem(index)" class="btn btn-sm btn-outline-danger px-2" title="Удалить">&times;</button>
                    </div>
                </div>

                <!-- Дочерняя зона: ВСЕГДА видна, даже если пустая -->
                <div class="nested-wrapper ms-3">
                    <nested-component
                        :tasks="element.children"
                        @tree-changed="emit('tree-changed')"
                    />
                </div>
            </li>
        </template>
    </draggable>
</template>

<script setup>
import { ref, toRef } from 'vue';
import draggable from 'vuedraggable';

const props = defineProps({
    tasks: { type: Array, required: true }
});

const emit = defineEmits(['tree-changed']);

// Используем toRef, чтобы v-model мог безопасно обновлять массив, переданный из родителя
const tasksRef = toRef(props, 'tasks');
const expandedElements = ref([]);

const toggleExpand = (id) => {
    const i = expandedElements.value.indexOf(id);
    if (i === -1) expandedElements.value.push(id);
    else expandedElements.value.splice(i, 1);
};

const isExpanded = (id) => expandedElements.value.includes(id);

const addChildItem = (parentElement) => {
    if (!parentElement.children) parentElement.children = [];
    const newId = crypto.randomUUID?.() ?? Date.now() + Math.random();

    parentElement.children.push({
        id: newId,
        name: 'Новый компонент #' + String(newId).slice(-4),
        unit: 'шт', quantity: 1, children: []
    });

    if (!expandedElements.value.includes(parentElement.id)) {
        expandedElements.value.push(parentElement.id);
    }

    emit('tree-changed'); // Отправляем данные наверх для сохранения
};

const removeItem = (index) => {
    if (confirm('Удалить этот элемент и все его вложенные поддетали?')) {
        tasksRef.value.splice(index, 1);
        emit('tree-changed');
    }
};

const onUnitChange = (element) => {
    emit('tree-changed');
};
</script>

<style scoped>
.tree-node { display: block; }

.drag-area {
    min-height: 50px;
    padding: 4px 0 8px 0;
}

.nested-wrapper {
    position: relative;
    border-left: 2px dashed #b6d4fe;
    padding-left: 10px;
    min-height: 30px;
}

.action-column {
    width: 250px;
    flex-shrink: 0;
}

.drag-area:hover {
    background-color: rgba(13, 110, 253, 0.04);
    border-radius: 6px;
}

.sortable-ghost {
    opacity: 0.4;
    background-color: #cfe2ff !important;
    border: 1px dashed #0d6efd !important;
}
</style>
