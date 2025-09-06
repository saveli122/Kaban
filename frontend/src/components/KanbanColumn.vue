<script setup>
import { ref } from 'vue'
import Draggable from 'vuedraggable'
import { createTask, updateColumn, deleteColumn as apiDeleteColumn, moveTask } from '../api'
import TaskCard from './TaskCard.vue'
import AddItem from './AddItem.vue'
import EditableText from './EditableText.vue'

const props = defineProps({
  column: { type: Object, required: true },
  allColumns: { type: Array, default: () => [] }
})

const editing = ref(false)
const title = ref(props.column.name)

/* ===== helpers ===== */
function findCurrentColumn() {
  // берём «живой» объект колонки из allColumns (если родитель менял ссылку)
  return props.allColumns.find(c => c.id === props.column.id) || props.column
}

/* ===== DnD: фиксация изменений на бэке ===== */
async function onTasksChange(evt) {
  // evt.moved — перестановка в той же колонке
  // evt.added — перенос ЗДЕСЬ из другой колонки
  // evt.removed — перенос ИЗ этой колонки в другую
  const col = findCurrentColumn()

  // Всегда перенумеровываем локально задачи в этой колонке
  if (Array.isArray(col.tasks)) col.tasks.forEach((t, i) => (t.position = i + 1))

  // Случай: перестановка внутри той же колонки
  if (evt.moved) {
    const el = evt.moved.element
    try {
      await moveTask(el.id, col.id, evt.moved.newIndex + 1)
    } catch (e) {
      console.warn('moveTask (same column) failed', e)
    }
    return
  }

  // Случай: в эту колонку добавили задачу из другой
  if (evt.added) {
    const el = evt.added.element
    el.column_id = col.id
    try {
      await moveTask(el.id, col.id, evt.added.newIndex + 1)
    } catch (e) {
      console.warn('moveTask (cross column) failed', e)
    }
    return
  }

  // Случай: из этой колонки задачу утащили — просто перенумеровали, API вызовет при add в целевой
  if (evt.removed) {
    return
  }
}

/* ===== переименование колонки (оптимистично) ===== */
function cancelEdit() {
  editing.value = false
  title.value = props.column.name
}
async function saveTitle() {
  const newName = title.value?.trim()
  if (!newName || newName === props.column.name) return cancelEdit()

  const col = findCurrentColumn()
  const before = col.name
  col.name = newName
  editing.value = false

  try {
    await updateColumn(col.id, { name: newName })
  } catch (e) {
    col.name = before
    title.value = before
    alert('Не удалось переименовать колонку')
  }
}

/* ===== удаление колонки (оптимистично) ===== */
async function removeColumn() {
  if (!confirm('Удалить колонку вместе с задачами?')) return
  const idx = props.allColumns.findIndex(c => c.id === props.column.id)
  if (idx === -1) return
  const removed = props.allColumns.splice(idx, 1)[0]

  try {
    await apiDeleteColumn(removed.id)
  } catch (e) {
    props.allColumns.splice(idx, 0, removed)
    alert('Не удалось удалить колонку')
  }
}

/* ===== добавление задачи (оптимистично) ===== */
async function addTask(newTitle, description = '') {
  if (!newTitle) return
  const col = findCurrentColumn()
  const pos = (col.tasks?.length || 0) + 1
  const tempId = Math.random()

  const optimistic = {
    id: tempId,
    title: newTitle,
    description,
    position: pos,
    column_id: col.id,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  }
  col.tasks = [...(col.tasks || []), optimistic]

  try {
    const created = await createTask(col.id, newTitle, description, pos)
    const data = created?.id ? created : (created?.data || created)
    const i = col.tasks.findIndex(t => t.id === tempId)
    if (i !== -1) col.tasks.splice(i, 1, { ...optimistic, ...data })
  } catch (e) {
    col.tasks = col.tasks.filter(t => t.id !== tempId)
    alert('Не удалось создать задачу')
  }
}
</script>

<template>
  <div class="column">
    <div class="column-title">
      <EditableText
          v-if="editing"
          v-model="title"
          @save="saveTitle"
          @cancel="cancelEdit"
      />
      <h3 v-else>{{ column.name }}</h3>

      <div class="column-actions">
        <button type="button" class="btn-ghost" title="Переименовать" @click="editing = true">✎</button>
        <button type="button" class="btn-ghost" title="Удалить" @click="removeColumn">✕</button>
      </div>
    </div>

    <!-- DnD-список задач -->
    <Draggable handle=".card-title"
        class="tasks droppable"
        :list="column.tasks"
        item-key="id"
        group="tasks"                <!-- разрешаем перенос между колонками -->
    :animation="150"
    ghost-class="drag-ghost"
    chosen-class="drag-chosen"
    @change="onTasksChange"
    >
    <template #item="{ element }">
      <TaskCard :task="element" :all-columns="allColumns" />
    </template>
    </Draggable>

    <div class="card">
      <AddItem
          placeholder="Новая задача"
          submit-label="Добавить задачу"
          with-description
          @submit="addTask"
      />
    </div>

    <div v-if="!column.tasks || column.tasks.length === 0" class="empty">Пока нет задач</div>
  </div>
</template>
