<script setup>
import { ref, onMounted } from 'vue'
import { updateTask, deleteTask, moveTask } from '../api'
import EditableText from './EditableText.vue'

const props = defineProps({
  task: { type: Object, required: true },
  allColumns: { type: Array, default: () => [] }
})
// локальные состояния редакторов
const editTitle = ref(false)
const editDesc  = ref(false)
const title = ref(props.task.title)
const description = ref(props.task.description || '')
// текущая колонка для select'a статуса
const selectedColumnId = ref(props.task.column_id ?? null)
onMounted(() => {
  if (selectedColumnId.value == null) {
    const found = props.allColumns.find(c => (c.tasks || []).some(t => t.id === props.task.id))
    if (found) selectedColumnId.value = found.id
  }
})
/* ===== helpers (без мутации пропсов) ===== */
function findColumnById(id) {
  return props.allColumns.find(c => c.id === Number(id))
}
function findColumnOfTask(taskId) {
  return props.allColumns.find(c => (c.tasks || []).some(t => t.id === taskId))
}
/* ===== смена статуса (реактивно) ===== */
async function changeStatus () {
  const targetId = Number(selectedColumnId.value)
  if (!targetId) return

  const from = findColumnOfTask(props.task.id)
  const to   = findColumnById(targetId)
  if (!from || !to || from.id === to.id) return

  const fromIndex = from.tasks.findIndex(t => t.id === props.task.id)
  if (fromIndex === -1) return
  // оптимистично переносим
  const oldTask = from.tasks[fromIndex]
  const newTask = { ...oldTask, column_id: targetId, position: (to.tasks?.length || 0) + 1 }
  from.tasks.splice(fromIndex, 1)
  to.tasks = [...(to.tasks || []), newTask]

  try {
    await moveTask(oldTask.id, targetId, newTask.position)
  } catch (e) {
    // откат
    to.tasks.pop()
    from.tasks.splice(fromIndex, 0, oldTask)
    selectedColumnId.value = from.id
    alert('Не удалось сменить статус')
  }
}
/* ===== заголовок ===== */
function cancelTitle() { editTitle.value = false; title.value = props.task.title }
async function saveTitle () {
  const newTitle = title.value?.trim()
  if (!newTitle || newTitle === props.task.title) return cancelTitle()

  const col = findColumnOfTask(props.task.id)
  if (!col) return cancelTitle()
  const idx = col.tasks.findIndex(t => t.id === props.task.id)
  if (idx === -1) return cancelTitle()

  // оптимистично обновляем элемент массива (без мутации пропа)
  const before = col.tasks[idx]
  const patched = { ...before, title: newTitle }
  col.tasks.splice(idx, 1, patched)
  editTitle.value = false

  try {
    await updateTask(before.id, { title: newTitle })
  } catch (e) {
    // откат
    col.tasks.splice(idx, 1, before)
    title.value = before.title
    alert('Не удалось сохранить заголовок')
  }
}
/* ===== описание ===== */
function cancelDesc() { editDesc.value = false; description.value = props.task.description || '' }
async function saveDesc () {
  const newDesc = description.value ?? ''
  const col = findColumnOfTask(props.task.id)
  if (!col) return cancelDesc()
  const idx = col.tasks.findIndex(t => t.id === props.task.id)
  if (idx === -1) return cancelDesc()

  const before = col.tasks[idx]
  const patched = { ...before, description: newDesc }
  col.tasks.splice(idx, 1, patched)
  editDesc.value = false

  try {
    await updateTask(before.id, { description: newDesc })
  } catch (e) {
    col.tasks.splice(idx, 1, before)
    description.value = before.description || ''
    alert('Не удалось сохранить описание')
  }
}
/* ===== удаление ===== */
async function removeTask () {
  if (!confirm('Удалить задачу?')) return
  const col = findColumnOfTask(props.task.id)
  if (!col) return

  const idx = col.tasks.findIndex(t => t.id === props.task.id)
  if (idx === -1) return
  const removed = col.tasks.splice(idx, 1)[0]  // оптимистично удалили

  try {
    await deleteTask(removed.id)
  } catch (e) {
    // откат
    col.tasks.splice(idx, 0, removed)
    alert('Не удалось удалить задачу')
  }
}

/* ===== даты ===== */
const dtFmt = new Intl.DateTimeFormat('ru-RU', {
  day: '2-digit', month: '2-digit', year: '2-digit', hour: '2-digit', minute: '2-digit'
})
function fmt (d) { if (!d) return '—'; const t = new Date(d); return isNaN(t) ? '—' : dtFmt.format(t) }
</script>

<template>
  <div class="card">
    <div class="card-title">
      <EditableText v-if="editTitle" v-model="title" @save="saveTitle" @cancel="cancelTitle" />
      <template v-else>{{ props.task.title }}</template>
    </div>

    <div class="card-desc">
      <template v-if="editDesc">
        <textarea class="textarea" v-model="description" placeholder="Описание"></textarea>
        <div class="card-actions">
          <button type="button" class="btn" @click="saveDesc">Сохранить</button>
          <button type="button" class="btn-ghost" @click="cancelDesc">Отмена</button>
        </div>
      </template>
      <template v-else>
        <div v-if="props.task.description">{{ props.task.description }}</div>
        <div v-else class="helper">Нет описания</div>
      </template>
    </div>

    <div class="status-row" v-if="allColumns && allColumns.length">
      <label>Статус</label>
      <select class="select" v-model="selectedColumnId" @change="changeStatus">
        <option v-for="c in allColumns" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
    </div>

    <div class="card-meta">
      <span title="Дата создания">Crt: {{ fmt(props.task.created_at) }}</span>
      <span title="Дата изменения">Upd: {{ fmt(props.task.updated_at) }}</span>
    </div>

    <div class="card-actions card-actions--hover">
    <div class="card-actions">
      <button type="button" class="btn-ghost" @click="editTitle = true">Переименовать</button>
      <button type="button" class="btn-ghost" @click="editDesc = true">Описание</button>
      <button type="button" class="btn-ghost" style="margin-left:auto;color:var(--danger)" @click="removeTask">Удалить</button>
    </div>
  </div>
  </div>
</template>
